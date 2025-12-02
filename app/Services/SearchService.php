<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\Lead;
use App\Models\Partner;
use App\Models\Product;
use App\Models\Application;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class SearchService
{
    protected $query;
    protected $limit;
    protected $cacheEnabled;

    public function __construct($query, $limit = 50, $cacheEnabled = true)
    {
        $this->query = $this->sanitizeQuery($query);
        $this->limit = $limit;
        $this->cacheEnabled = $cacheEnabled;
    }

    /**
     * Sanitize and validate search query
     */
    protected function sanitizeQuery($query)
    {
        // Remove dangerous characters but keep useful ones
        $query = strip_tags($query);
        $query = trim($query);
        
        return $query;
    }

    /**
     * Main search method
     */
    public function search()
    {
        if (strlen($this->query) < 2) {
            return $this->emptyResponse();
        }

        $cacheKey = 'search:' . md5($this->query . ':' . $this->limit);

        if ($this->cacheEnabled) {
            return Cache::remember($cacheKey, 300, function () {
                return $this->performSearch();
            });
        }

        return $this->performSearch();
    }

    /**
     * Perform the actual search
     */
    protected function performSearch()
    {
        $results = [];

        // Parse query for special searches
        $searchType = $this->detectSearchType();

        switch ($searchType['type']) {
            case 'client_id':
                $results = array_merge($results, $this->searchByClientId($searchType['value']));
                break;
            case 'email':
                $results = array_merge($results, $this->searchByEmail($searchType['value']));
                break;
            case 'phone':
                $results = array_merge($results, $this->searchByPhone($searchType['value']));
                break;
            default:
                // General search across all types
                $results = array_merge(
                    $this->searchClients(),
                    $this->searchLeads(),
                    $this->searchPartners(),
                    $this->searchProducts(),
                    $this->searchApplications()
                );
                break;
        }

        return ['items' => $results];
    }

    /**
     * Detect special search patterns
     */
    protected function detectSearchType()
    {
        // Check for client ID pattern (e.g., #123, CLI-123)
        if (preg_match('/^#?(\d+)$/', $this->query, $matches)) {
            return ['type' => 'client_id', 'value' => $matches[1]];
        }

        // Check for email pattern
        if (filter_var($this->query, FILTER_VALIDATE_EMAIL)) {
            return ['type' => 'email', 'value' => $this->query];
        }

        // Check for phone pattern (contains only digits and common separators)
        if (preg_match('/^[\d\s\-\+\(\)]+$/', $this->query)) {
            return ['type' => 'phone', 'value' => preg_replace('/[^\d]/', '', $this->query)];
        }

        return ['type' => 'general', 'value' => $this->query];
    }

    /**
     * Search clients
     */
    protected function searchClients()
    {
        $query = $this->query;
        $dob = $this->parseDOB($query);

        $phoneSubquery = DB::table('client_phones')
            ->select('client_id', DB::raw('GROUP_CONCAT(client_phone) as phones'))
            ->groupBy('client_id');

        $clients = Admin::where('admins.role', '=', 7)
            ->where(function ($q) {
                $q->whereNull('admins.is_deleted')
                  ->orWhere('admins.is_deleted', 0);
            })
            ->leftJoinSub($phoneSubquery, 'phone_data', 'admins.id', '=', 'phone_data.client_id')
            ->where(function ($q) use ($query, $dob) {
                $q->where('admins.email', 'LIKE', '%' . $query . '%')
                  ->orWhere('admins.first_name', 'LIKE', '%' . $query . '%')
                  ->orWhere('admins.last_name', 'LIKE', '%' . $query . '%')
                  ->orWhere('admins.client_id', 'LIKE', '%' . $query . '%')
                  ->orWhere('admins.att_email', 'LIKE', '%' . $query . '%')
                  ->orWhere('admins.att_phone', 'LIKE', '%' . $query . '%')
                  ->orWhere('admins.phone', 'LIKE', '%' . $query . '%')
                  ->orWhere(DB::raw("CONCAT(admins.first_name, ' ', admins.last_name)"), 'LIKE', '%' . $query . '%')
                  ->orWhere('phone_data.phones', 'LIKE', '%' . $query . '%');
                
                if ($dob) {
                    $q->orWhere('admins.dob', '=', $dob);
                }
            })
            ->select('admins.*', 'phone_data.phones')
            ->limit($this->limit)
            ->get();

        return $clients->map(function ($client) {
            return [
                'name' => $this->highlightMatch($client->first_name . ' ' . $client->last_name),
                'email' => $this->highlightMatch($client->email ?? ''),
                'client_id' => $client->client_id,
                'status' => $client->is_archived == 1 ? 'Archived' : ($client->type ?? 'Client'),
                'type' => 'Client',
                'id' => base64_encode(convert_uuencode($client->id)) . '/Client',
                'raw_id' => $client->id,
                'category' => 'clients',
                'badge_color' => $client->is_archived == 1 ? 'gray' : 'yellow'
            ];
        })->toArray();
    }

    /**
     * Search leads
     */
    protected function searchLeads()
    {
        $query = $this->query;
        $dob = $this->parseDOB($query);

        $leads = Lead::where('converted', '=', 0)
            ->where(function ($q) use ($query, $dob) {
                $q->where('email', 'LIKE', '%' . $query . '%')
                  ->orWhere('first_name', 'LIKE', '%' . $query . '%')
                  ->orWhere('last_name', 'LIKE', '%' . $query . '%')
                  ->orWhere('phone', 'LIKE', '%' . $query . '%')
                  ->orWhere(DB::raw("CONCAT(first_name, ' ', last_name)"), 'LIKE', '%' . $query . '%');
                
                if ($dob) {
                    $q->orWhere('dob', '=', $dob);
                }
            })
            ->limit($this->limit)
            ->get();

        return $leads->map(function ($lead) {
            return [
                'name' => $this->highlightMatch($lead->first_name . ' ' . $lead->last_name),
                'email' => $this->highlightMatch($lead->email ?? ''),
                'status' => 'Lead',
                'type' => 'Lead',
                'id' => base64_encode(convert_uuencode($lead->id)) . '/Lead',
                'raw_id' => $lead->id,
                'category' => 'leads',
                'badge_color' => 'blue'
            ];
        })->toArray();
    }

    /**
     * Search partners
     */
    protected function searchPartners()
    {
        $query = $this->query;

        $partners = Partner::where(function ($q) use ($query) {
                $q->where('partner_name', 'LIKE', '%' . $query . '%')
                  ->orWhere('email', 'LIKE', '%' . $query . '%')
                  ->orWhere('phone', 'LIKE', '%' . $query . '%')
                  ->orWhere('partner_id', 'LIKE', '%' . $query . '%');
            })
            ->limit($this->limit)
            ->get();

        return $partners->map(function ($partner) {
            return [
                'name' => $this->highlightMatch($partner->partner_name ?? ''),
                'email' => $this->highlightMatch($partner->email ?? ''),
                'partner_id' => $partner->partner_id,
                'status' => 'Partner',
                'type' => 'Partner',
                'id' => base64_encode(convert_uuencode($partner->id)) . '/Partner',
                'raw_id' => $partner->id,
                'category' => 'partners',
                'badge_color' => 'purple'
            ];
        })->toArray();
    }

    /**
     * Search products
     */
    protected function searchProducts()
    {
        $query = $this->query;

        $products = Product::where(function ($q) use ($query) {
                $q->where('product_name', 'LIKE', '%' . $query . '%')
                  ->orWhere('product_code', 'LIKE', '%' . $query . '%');
            })
            ->limit($this->limit)
            ->get();

        return $products->map(function ($product) {
            return [
                'name' => $this->highlightMatch($product->product_name ?? ''),
                'email' => $product->product_code ?? '',
                'status' => 'Product',
                'type' => 'Product',
                'id' => base64_encode(convert_uuencode($product->id)) . '/Product',
                'raw_id' => $product->id,
                'category' => 'products',
                'badge_color' => 'green'
            ];
        })->toArray();
    }

    /**
     * Search applications
     */
    protected function searchApplications()
    {
        $query = $this->query;

        $applications = Application::where(function ($q) use ($query) {
                $q->where('application_id', 'LIKE', '%' . $query . '%')
                  ->orWhere('student_id', 'LIKE', '%' . $query . '%');
            })
            ->limit($this->limit)
            ->get();

        return $applications->map(function ($app) {
            return [
                'name' => $this->highlightMatch('Application #' . ($app->application_id ?? $app->id)),
                'email' => 'Student ID: ' . ($app->student_id ?? 'N/A'),
                'status' => 'Application',
                'type' => 'Application',
                'id' => base64_encode(convert_uuencode($app->id)) . '/Application',
                'raw_id' => $app->id,
                'category' => 'applications',
                'badge_color' => 'indigo'
            ];
        })->toArray();
    }

    /**
     * Search by specific client ID
     */
    protected function searchByClientId($clientId)
    {
        $clients = Admin::where('role', '=', 7)
            ->where(function ($q) {
                $q->whereNull('is_deleted')->orWhere('is_deleted', 0);
            })
            ->where(function ($q) use ($clientId) {
                $q->where('client_id', 'LIKE', '%' . $clientId . '%')
                  ->orWhere('id', '=', $clientId);
            })
            ->limit(10)
            ->get();

        return $clients->map(function ($client) {
            return [
                'name' => $client->first_name . ' ' . $client->last_name,
                'email' => $client->email ?? '',
                'client_id' => $client->client_id,
                'status' => $client->is_archived == 1 ? 'Archived' : ($client->type ?? 'Client'),
                'type' => 'Client',
                'id' => base64_encode(convert_uuencode($client->id)) . '/Client',
                'raw_id' => $client->id,
                'category' => 'clients',
                'badge_color' => 'yellow'
            ];
        })->toArray();
    }

    /**
     * Search by email
     */
    protected function searchByEmail($email)
    {
        $results = [];

        // Search clients
        $clients = Admin::where('role', '=', 7)
            ->where(function ($q) {
                $q->whereNull('is_deleted')->orWhere('is_deleted', 0);
            })
            ->where('email', 'LIKE', '%' . $email . '%')
            ->limit(10)
            ->get();

        foreach ($clients as $client) {
            $results[] = [
                'name' => $client->first_name . ' ' . $client->last_name,
                'email' => $client->email ?? '',
                'status' => $client->type ?? 'Client',
                'type' => 'Client',
                'id' => base64_encode(convert_uuencode($client->id)) . '/Client',
                'category' => 'clients',
                'badge_color' => 'yellow'
            ];
        }

        // Search leads
        $leads = Lead::where('converted', 0)
            ->where('email', 'LIKE', '%' . $email . '%')
            ->limit(10)
            ->get();

        foreach ($leads as $lead) {
            $results[] = [
                'name' => $lead->first_name . ' ' . $lead->last_name,
                'email' => $lead->email ?? '',
                'status' => 'Lead',
                'type' => 'Lead',
                'id' => base64_encode(convert_uuencode($lead->id)) . '/Lead',
                'category' => 'leads',
                'badge_color' => 'blue'
            ];
        }

        return $results;
    }

    /**
     * Search by phone
     */
    protected function searchByPhone($phone)
    {
        $results = [];

        // Search clients
        $clients = Admin::where('role', '=', 7)
            ->where(function ($q) {
                $q->whereNull('is_deleted')->orWhere('is_deleted', 0);
            })
            ->where(function ($q) use ($phone) {
                $q->where('phone', 'LIKE', '%' . $phone . '%')
                  ->orWhere('att_phone', 'LIKE', '%' . $phone . '%');
            })
            ->limit(10)
            ->get();

        foreach ($clients as $client) {
            $results[] = [
                'name' => $client->first_name . ' ' . $client->last_name,
                'email' => $client->email ?? '',
                'status' => $client->type ?? 'Client',
                'type' => 'Client',
                'id' => base64_encode(convert_uuencode($client->id)) . '/Client',
                'category' => 'clients',
                'badge_color' => 'yellow'
            ];
        }

        return $results;
    }

    /**
     * Parse DOB from query
     */
    protected function parseDOB($query)
    {
        if (strstr($query, '/')) {
            $dob = explode('/', $query);
            if (!empty($dob) && is_array($dob) && count($dob) == 3) {
                return $dob[2] . '/' . $dob[1] . '/' . $dob[0];
            }
        }
        return null;
    }

    /**
     * Highlight matching text
     */
    protected function highlightMatch($text)
    {
        if (empty($text)) {
            return $text;
        }

        $query = preg_quote($this->query, '/');
        return preg_replace(
            '/(' . $query . ')/i',
            '<mark class="search-highlight">$1</mark>',
            $text
        );
    }

    /**
     * Empty response
     */
    protected function emptyResponse()
    {
        return ['items' => []];
    }
}


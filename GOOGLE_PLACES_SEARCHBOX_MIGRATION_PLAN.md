# Google Places SearchBox Migration Plan

## Current Status
The application uses `google.maps.places.SearchBox` which is deprecated for new customers as of March 1, 2025.

## Deprecation Notice
- **Deprecation Date**: March 1, 2025 (for new customers)
- **Status**: Still functional, will continue to receive bug fixes
- **Discontinuation**: At least 12 months notice will be given before support is discontinued
- **Reference**: [Google Maps Legacy Documentation](https://developers.google.com/maps/legacy)

## Current Implementation
- **File**: `resources/views/Admin/clients/edit.blade.php`
- **File**: `resources/views/Admin/leads/create.blade.php`
- **Usage**: Address autocomplete functionality for client/lead forms

## Migration Plan

### Phase 1: Assessment (Future)
- [ ] Identify all instances of `SearchBox` usage
- [ ] Document current functionality and requirements
- [ ] Test `Autocomplete` API compatibility

### Phase 2: Migration to Autocomplete (Future)
Replace `SearchBox` with `Autocomplete`:

```javascript
// Old (SearchBox)
const searchBox = new google.maps.places.SearchBox(input);

// New (Autocomplete)
const autocomplete = new google.maps.places.Autocomplete(input, {
  types: ['geocode'],
  componentRestrictions: { country: ['au', 'nz'] } // Adjust as needed
});
```

### Phase 3: Testing (Future)
- [ ] Test address autocomplete functionality
- [ ] Verify address parsing and auto-population
- [ ] Test on all affected forms

## Priority
**Low** - Current implementation is functional. Plan migration when:
- Google announces discontinuation date
- New features require Autocomplete API
- Performance improvements are needed

## Notes
- Migration is not urgent but should be planned
- Autocomplete API offers better performance and more features
- Code comments have been added to mark deprecated usage


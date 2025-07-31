# CV Management Component Documentation

## Overview
The CV Management component is a complete Livewire Volt class-based component that provides full CRUD (Create, Read, Update, Delete) functionality for managing candidate CVs in the HR management system.

## Features

### Core Functionality
- **Create CV**: Add new CV records with comprehensive personal and professional information
- **View CV**: Display detailed CV information in a modal
- **Edit CV**: Update existing CV records
- **Delete CV**: Remove CV records with confirmation
- **File Upload**: Upload and manage CV documents using Spatie Media Library
- **Download CV**: Download uploaded CV files

### Search and Filtering
- **Search**: Search by name, email, phone, and nationality
- **Filter by Gender**: Filter CVs by male, female, or other
- **Filter by Marital Status**: Filter by single, married, divorced, or widowed
- **Filter by Nationality**: Filter by nationality
- **Clear Filters**: Reset all applied filters

### Data Fields
The component handles the following CV information:
- Personal Information: name, email, phone, address, birth date, gender, marital status
- Location: country, state, city
- Professional: nationality, religion, summary, skills, experience, education
- Additional: projects, certifications, languages, interests, references, cover letter, portfolio
- File: CV document upload

## File Structure

```
resources/views/livewire/hr-management/cvs/manage-cvs.blade.php
├── PHP Class (Livewire Volt Component)
│   ├── Properties with validation rules
│   ├── CRUD methods (create, store, edit, update, delete)
│   ├── File handling methods
│   ├── Search and filter methods
│   └── Computed properties
└── Blade Template
    ├── Search and filter interface
    ├── CV listing table
    ├── Create/Edit modal
    ├── View details modal
    └── Delete confirmation modal
```

## Usage

### Accessing the Component
The component is accessible via the route `/cvs` and is integrated into the admin dashboard.

### Navigation
1. Go to the admin dashboard
2. Navigate to HR Management → CVs
3. The component will display the CV management interface

### Adding a New CV
1. Click the "Add New CV" button
2. Fill in the required fields (name, phone, birth date, gender, marital status, nationality, religion)
3. Optionally fill in additional fields
4. Upload a CV file (optional)
5. Click "Save CV"

### Viewing CV Details
1. Click the "View" button on any CV row
2. A modal will display all CV information
3. You can download the CV file if uploaded
4. Access edit or delete options from the view modal

### Editing a CV
1. Click the "Edit" button on any CV row
2. Modify the required fields
3. Update the CV file if needed
4. Click "Update CV"

### Deleting a CV
1. Click the "Delete" button on any CV row
2. Confirm the deletion in the confirmation modal
3. The CV will be permanently removed

### Searching and Filtering
- Use the search box to find CVs by name, email, phone, or nationality
- Use the dropdown filters to narrow results by gender, marital status, or nationality
- Click "Clear Filters" to reset all filters

## Technical Implementation

### Dependencies
- **Laravel**: Core framework
- **Livewire**: Real-time component functionality
- **Spatie Media Library**: File upload and management
- **Bootstrap**: UI components and styling

### Key Methods

#### CRUD Operations
- `create()`: Initialize form for new CV
- `store()`: Save new CV with validation
- `edit($id)`: Load CV data for editing
- `update()`: Update existing CV
- `delete($id)`: Mark CV for deletion
- `confirmDelete()`: Permanently delete CV

#### File Management
- `downloadCv($id)`: Download CV file
- File upload handling in store/update methods

#### Search and Filter
- `updatedSearch()`: Handle search input changes
- `updatedFilterGender()`: Handle gender filter changes
- `updatedFilterMaritalStatus()`: Handle marital status filter changes
- `updatedFilterNationality()`: Handle nationality filter changes
- `clearFilters()`: Reset all filters

#### Computed Properties
- `getCvsProperty()`: Get paginated CV list with search/filter
- `getGenderOptionsProperty()`: Gender dropdown options
- `getMaritalStatusOptionsProperty()`: Marital status dropdown options
- `getNationalityOptionsProperty()`: Nationality dropdown options

### Validation Rules
All form fields have appropriate validation rules:
- Required fields: name, phone, birth_date, gender, marital_status, nationality, religion
- Email validation for email field
- String length limits for text fields
- Enum validation for gender and marital status

### Database Integration
- Uses the `cvs` table created by migration `2025_07_28_180408_create_cvs_table.php`
- Integrates with Spatie Media Library for file management
- Uses the `HR_Cvs` media collection for CV documents

## Sample Data
The component includes a `CvSeeder` that provides sample CV data for testing:
- Ahmed Hassan (Software Developer)
- Fatima Al-Zahra (UI/UX Designer)
- Omar Abdullah (Project Manager)

## Security Features
- Form validation on both client and server side
- File upload validation and security
- CSRF protection via Livewire
- Authentication middleware protection

## Responsive Design
The component is fully responsive and works on:
- Desktop computers
- Tablets
- Mobile devices

## Browser Compatibility
- Modern browsers (Chrome, Firefox, Safari, Edge)
- JavaScript enabled required for Livewire functionality
- Bootstrap 5 compatible

## Troubleshooting

### Common Issues
1. **File upload not working**: Ensure proper disk configuration and permissions
2. **Search not working**: Check if the search field is properly bound
3. **Modal not showing**: Verify Bootstrap JavaScript is loaded
4. **Validation errors**: Check that all required fields are filled

### Debug Tips
- Check browser console for JavaScript errors
- Review Laravel logs for PHP errors
- Verify database connection and table structure
- Ensure all required dependencies are installed

## Future Enhancements
Potential improvements for the component:
- Bulk operations (import/export)
- Advanced filtering options
- CV parsing from uploaded files
- Integration with job applications
- Email notifications
- CV status tracking
- Interview scheduling integration 
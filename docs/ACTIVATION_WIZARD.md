# Application Activation Wizard

## Overview

The Application Activation Wizard is a user-friendly, step-by-step interface for activating applications in companies within MultiFlexi. It simplifies the process of creating and configuring RunTemplates.

## Features

- **4-Step Process**: Guided workflow for application activation
- **Visual Selection**: Card-based interface for choosing companies and applications
- **Progress Tracking**: Step indicator showing current position in the wizard
- **Session Persistence**: Wizard state is maintained across page reloads
- **Validation**: Each step validates data before proceeding
- **Responsive Design**: Works on desktop and mobile devices

## Wizard Steps

### Step 1: Choose Company

Select the company where you want to activate the application.

- Displays all available companies with logos
- Shows company ID (IČO) for identification
- Card-based selection with visual feedback
- Empty state handling with link to create new company

### Step 2: Choose Application

Select the application to activate for the chosen company.

- Shows only enabled applications
- Displays application icons and descriptions
- Filters based on company context
- Visual card selection interface

### Step 3: Create RunTemplate

Configure the RunTemplate for the application.

- **Name**: Descriptive name for the configuration
- **Schedule Interval**: How often the application should run
  - 🔴 Disabled
  - ⏳ Every minute
  - 🕰️ Hourly
  - ☀️ Daily
  - 📅 Weekly
  - 🌛 Monthly
  - 🎆 Yearly

### Step 4: Configure

Set application-specific configuration fields.

- Dynamic form generation based on application requirements
- Support for different field types:
  - String/text inputs
  - Password fields
  - Boolean checkboxes
  - Integer number inputs
  - File path uploads
- Field descriptions and validation
- Empty state handling for applications without configuration

## Technical Implementation

### Files

- **Page**: `debian/multiflexi-web/usr/share/multiflexi/activation-wizard.php`
- **UI Component**: `src/MultiFlexi/Ui/ActivationWizard.php`
- **Menu Integration**: `src/MultiFlexi/Ui/MainMenu.php`

### Session Management

Wizard data is stored in `$_SESSION['activation_wizard']` with the following structure:

```php
[
    'company_id' => int|null,
    'app_id' => int|null,
    'runtemplate_name' => string|null,
    'runtemplate_id' => int|null,
    'configuration' => array,
]
```

Session is cleared upon completion or can be manually reset.

### Navigation Flow

```
Step 1 (Company Selection)
    ↓ POST company_id
Step 2 (Application Selection)
    ↓ POST app_id
Step 3 (RunTemplate Creation)
    ↓ POST runtemplate_name, interv
    ↓ CREATE RunTemplate in database
Step 4 (Configuration)
    ↓ POST configuration fields
    ↓ SAVE to Configuration table
Finish → Redirect to RunTemplate page
```

### CSS Styling

Custom styles provide:
- Smooth transitions on card hover
- Visual feedback for selected items
- Proper spacing and layout
- Border highlighting for active selections

### JavaScript Enhancements

- Click-to-select functionality for cards
- Automatic radio button selection
- Visual state changes on selection

## Usage

### Accessing the Wizard

1. Log in to MultiFlexi
2. Navigate to **Applications** menu
3. Click **Activation Wizard** (🧙)

### Completing the Wizard

1. **Choose Company**: Click on a company card to select it, then click "Next"
2. **Choose Application**: Click on an application card to select it, then click "Next"
3. **Create RunTemplate**: Enter a name and select scheduling interval, then click "Next"
4. **Configure**: Fill in required configuration fields, then click "Finish & Activate"
5. You will be redirected to the RunTemplate configuration page

### Navigation

- **Next**: Proceed to the next step (validates current step)
- **Previous**: Go back to the previous step (data is preserved)
- **Finish & Activate**: Complete the wizard and save all data

### Back Button Behavior

Using the browser back button is safe - the wizard maintains state in the session. However, it's recommended to use the "Previous" button for better UX.

## Error Handling

### Empty States

- **No Companies**: Shows alert with link to create a company
- **No Applications**: Shows alert indicating no applications available
- **Missing Data**: Prevents progression with validation messages

### Validation

- Company selection is required in Step 1
- Application selection is required in Step 2
- RunTemplate name is required in Step 3
- Required configuration fields are enforced in Step 4

## Integration Points

### Database Tables

- `company`: Company records
- `apps`: Application definitions
- `runtemplate`: RunTemplate records
- `configuration`: RunTemplate configuration values
- `conffield`: Application environment field definitions

### Classes Used

- `\MultiFlexi\Company`
- `\MultiFlexi\Application`
- `\MultiFlexi\RunTemplate`
- `\MultiFlexi\Configuration`
- `\MultiFlexi\Conffield`
- `\MultiFlexi\ConfigField`
- `\MultiFlexi\ConfigFields`

## Customization

### Adding New Steps

To add additional steps:

1. Update `$totalSteps` in `ActivationWizard` class
2. Add step handling in `renderStepContent()` switch statement
3. Create new render method for the step
4. Add case in wizard page switch statement for data processing
5. Update navigation flow

### Field Type Support

To support additional field types:

1. Add new case in `createConfigInput()` method
2. Implement appropriate HTML input type
3. Handle value extraction in configuration save logic

## Best Practices

1. **Always validate** user input at each step
2. **Clear wizard data** upon completion
3. **Handle empty states** gracefully
4. **Provide visual feedback** for selections
5. **Preserve session data** for navigation
6. **Use descriptive names** for RunTemplates
7. **Test with various field types** during development

## Future Enhancements

Potential improvements:
- AJAX-based step navigation (no page reload)
- Real-time validation
- Step completion persistence across sessions
- Bulk activation for multiple applications
- Template copying from existing configurations
- Import/export wizard configurations
- Credential assignment in wizard flow
- Preview before final activation

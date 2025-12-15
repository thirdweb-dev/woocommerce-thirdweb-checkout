# Contributing to thirdweb WooCommerce Checkout

Thank you for your interest in contributing to the thirdweb WooCommerce Checkout plugin! This document provides guidelines and instructions for contributing.

## Table of Contents

- [Code of Conduct](#code-of-conduct)
- [Getting Started](#getting-started)
- [Development Setup](#development-setup)
- [Making Changes](#making-changes)
- [Submitting Pull Requests](#submitting-pull-requests)
- [Code Style](#code-style)
- [Testing](#testing)
- [Release Process](#release-process)

## Code of Conduct

This project follows thirdweb's community guidelines. Please be respectful and constructive in all interactions.

## Getting Started

### Prerequisites

- Node.js 16+ and pnpm
- PHP 7.4+
- WordPress 6.0+ with WooCommerce 8.0+
- Git
- A local WordPress development environment (LocalWP, XAMPP, Docker, etc.)

### Areas for Contribution

We welcome contributions in these areas:

- 🐛 **Bug Fixes**: Fix reported issues
- ✨ **Features**: Add new functionality
- 📚 **Documentation**: Improve guides and code comments
- 🎨 **UI/UX**: Enhance the checkout experience
- 🧪 **Testing**: Add test coverage
- 🌐 **Translations**: Add language support
- ⚡ **Performance**: Optimize code and assets

## Development Setup

### 1. Fork and Clone

```bash
# Fork the repo on GitHub, then clone your fork
git clone https://github.com/YOUR_USERNAME/woocommerce-thirdweb-checkout.git
cd woocommerce-thirdweb-checkout
```

### 2. Install Dependencies

```bash
# Install Node dependencies
pnpm install
```

### 3. Configure Environment

```bash
# Copy example environment file
cp .env.example .env

# Edit .env and add your thirdweb Client ID
# THIRDWEB_CLIENT_ID=your_client_id_here
```

### 4. Build the Plugin

```bash
# Development build with watch mode
pnpm run start

# Or production build
pnpm run build
```

### 5. Install in WordPress

**Option A: Symlink (Recommended for development)**

```bash
# Link to your local WordPress installation
ln -s $(pwd) /path/to/wordpress/wp-content/plugins/thirdweb-woocommerce-checkout

# Example with LocalWP
ln -s $(pwd) ~/Local\ Sites/YOUR_SITE/app/public/wp-content/plugins/thirdweb-woocommerce-checkout
```

**Option B: Copy Files**

```bash
# Copy plugin directory to WordPress
cp -r . /path/to/wordpress/wp-content/plugins/thirdweb-woocommerce-checkout
```

### 6. Activate Plugin

1. Log into WordPress admin
2. Go to **Plugins**
3. Find "thirdweb Stablecoin Checkout for WooCommerce"
4. Click **Activate**

## Making Changes

### Branch Naming

Use descriptive branch names:

- `feature/add-refund-support` - New features
- `fix/widget-loading-issue` - Bug fixes
- `docs/update-installation` - Documentation
- `refactor/payment-handler` - Code refactoring

### Development Workflow

1. **Create a branch**
   ```bash
   git checkout -b feature/your-feature-name
   ```

2. **Make your changes**
   - Edit source files in `src/` for React components
   - Edit PHP files in `includes/` for backend logic
   - Update documentation if needed

3. **Build and test**
   ```bash
   # Rebuild after changes
   pnpm run build

   # Test in your local WordPress installation
   ```

4. **Commit your changes**
   ```bash
   git add .
   git commit -m "feat: add refund support via thirdweb API"
   ```

   Use conventional commit messages:
   - `feat:` - New feature
   - `fix:` - Bug fix
   - `docs:` - Documentation changes
   - `style:` - Code style changes (formatting)
   - `refactor:` - Code refactoring
   - `test:` - Adding tests
   - `chore:` - Maintenance tasks

5. **Push to your fork**
   ```bash
   git push origin feature/your-feature-name
   ```

## Submitting Pull Requests

### Before Submitting

- [ ] Code builds without errors (`pnpm run build`)
- [ ] Changes tested in local WordPress environment
- [ ] Documentation updated if needed
- [ ] No console errors or warnings
- [ ] Follows code style guidelines

### PR Process

1. **Create Pull Request**
   - Go to the [repository](https://github.com/thirdweb-dev/woocommerce-thirdweb-checkout)
   - Click "New Pull Request"
   - Select your fork and branch
   - Fill in the PR template

2. **PR Description Template**
   ```markdown
   ## Description
   Brief description of changes

   ## Type of Change
   - [ ] Bug fix
   - [ ] New feature
   - [ ] Documentation update
   - [ ] Performance improvement

   ## Testing
   How were these changes tested?

   ## Screenshots (if applicable)
   Add screenshots for UI changes

   ## Checklist
   - [ ] Code builds successfully
   - [ ] Tested in local WordPress environment
   - [ ] Documentation updated
   - [ ] No breaking changes
   ```

3. **Review Process**
   - Maintainers will review your PR
   - Address any requested changes
   - Once approved, it will be merged

## Code Style

### PHP Code Style

Follow WordPress PHP coding standards:

```php
// Use tabs for indentation
// Space after control structures
if ( $condition ) {
    do_something();
}

// Yoda conditions for comparisons
if ( 'value' === $variable ) {
    // ...
}

// Proper spacing
$array = array(
    'key1' => 'value1',
    'key2' => 'value2',
);
```

### TypeScript/React Code Style

Follow standard TypeScript and React conventions:

```typescript
// Use functional components
export const MyComponent: React.FC<Props> = ({ prop1, prop2 }) => {
    // Use hooks
    const [state, setState] = useState<string>('');

    // Clear, descriptive names
    const handleClick = () => {
        setState('new value');
    };

    return (
        <div className="my-component">
            {/* JSX content */}
        </div>
    );
};

// Use TypeScript types
interface Props {
    prop1: string;
    prop2: number;
}
```

### Comments

```php
/**
 * Function description
 *
 * @param string $param1 Parameter description
 * @param int    $param2 Another parameter
 * @return bool Returns true on success
 */
function my_function( $param1, $param2 ) {
    // Implementation
}
```

```typescript
/**
 * Component description
 *
 * @param props - Component props
 * @returns JSX element
 */
export const MyComponent: React.FC<Props> = (props) => {
    // Implementation
};
```

## Testing

### Manual Testing Checklist

- [ ] Plugin activates without errors
- [ ] Settings page loads correctly
- [ ] Checkout widget displays at checkout
- [ ] Wallet connection works
- [ ] Payment completes successfully
- [ ] Order status updates correctly
- [ ] Transaction hash saved in order notes
- [ ] No console errors
- [ ] Works with WooCommerce blocks
- [ ] Responsive on mobile devices

### Test Environment Setup

Use testnet for safe testing:

1. Configure plugin for testnet (e.g., Base Sepolia: `84532`)
2. Use testnet tokens from faucets
3. Test full payment flow
4. Verify no real funds are used

### Future: Automated Testing

We plan to add:
- Unit tests for PHP functions
- Integration tests for WooCommerce
- E2E tests with Playwright
- React component tests with Jest

## File Structure

```
woocommerce-thirdweb-checkout/
├── src/                          # React/TypeScript source
│   └── checkout-block/
│       ├── index.tsx            # Blocks registration
│       └── ThirdwebCheckout.tsx # Main widget component
├── includes/                     # PHP backend
│   ├── class-thirdweb-payment-gateway.php
│   └── class-thirdweb-blocks-support.php
├── build/                        # Compiled assets (git ignored)
├── assets/                       # Static assets (icons, etc.)
├── test-app/                     # Standalone React test app
├── .github/                      # GitHub Actions workflows
├── thirdweb-woocommerce-checkout.php  # Main plugin file
├── readme.txt                    # WordPress.org readme
├── README.md                     # GitHub readme
└── INSTALLATION.md              # Installation guide
```

## Release Process

Releases are handled by maintainers:

1. **Version Bump**
   - Update version in `thirdweb-woocommerce-checkout.php`
   - Update version in `readme.txt` changelog
   - Update `THIRDWEB_WC_VERSION` constant

2. **Create Release**
   ```bash
   # Tag the release
   git tag -a v1.0.0 -m "Release version 1.0.0"
   git push origin v1.0.0
   ```

3. **GitHub Actions**
   - Automatically builds plugin
   - Creates GitHub release
   - Uploads distribution ZIP

4. **WordPress.org** (manual)
   - Update SVN repository
   - Deploy to WordPress.org

## Getting Help

- 💬 [thirdweb Discord](https://discord.gg/thirdweb) - #support channel
- 🐛 [GitHub Issues](https://github.com/thirdweb-dev/woocommerce-thirdweb-checkout/issues)
- 📚 [thirdweb Docs](https://portal.thirdweb.com)

## Recognition

Contributors will be:
- Listed in release notes
- Mentioned in the Contributors section (if significant contribution)
- Added to GitHub contributors graph

Thank you for contributing to make Web3 payments accessible to everyone! 🚀

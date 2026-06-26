# Hdweb GenerateUrl - LLMs.txt File Generator for Magento 2

## Overview

This Magento 2 extension generates an `llms.txt` file that provides structured information about your store's products, categories, and CMS pages in a format optimized for Large Language Models (LLMs).

## Features

- **Configurable Content Generation**: Control what information is included in the LLMs.txt file
- **Product Pages**: Include product information with customizable fields
- **Category Pages**: Include category information with customizable fields
- **CMS Pages**: Include CMS page information with customizable fields
- **Access Control**: Define access control terms and conditions
- **Store View Support**: Configure settings per store view
- **Flexible Exclusions**: Exclude specific products, categories, or CMS pages

## Installation

1. Copy the extension to `app/code/Hdweb/GenerateUrl/`
2. Run the following commands:

```bash
php bin/magento module:enable Hdweb_GenerateUrl
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento cache:flush
```

## Configuration

Navigate to **Stores > Configuration > Hdweb > LLMs.txt File Generator** in the Magento admin panel.

### General Settings

- **Enable**: Enable/disable the extension
- **Company Name**: Your company name
- **Company Description**: Company description
- **Additional Information Block**: Additional information to include
- **LLMs.txt File Path**: Path relative to pub/ directory (default: `llms`)
- **Access Control**: Terms and conditions for access

### Product Pages

- **Enable Product Pages**: Enable/disable product inclusion
- **Additional Product Content Fields**: Select additional fields to include
- **Exclude Product IDs**: Comma-separated list of product IDs to exclude
- **Product Sort Order**: Sort order for products section

### Category Pages

- **Enable Category Pages**: Enable/disable category inclusion
- **Additional Category Content Fields**: Select additional fields to include
- **Exclude Category IDs**: Comma-separated list of category IDs to exclude
- **Category Sort Order**: Sort order for categories section

### CMS Pages

- **Enable CMS Pages**: Enable/disable CMS page inclusion
- **Additional CMS Content Fields**: Select additional fields to include
- **Exclude CMS Pages**: Select CMS pages to exclude
- **CMS Sort Order**: Sort order for CMS pages section
- **Restricted Pages**: One per line (e.g., /customer/*)
- **Additional Pages**: Markdown-style format: - [Title](URL): Content

## Accessing the LLMs.txt File

Once configured, the LLMs.txt file will be accessible at:

- `https://yourstore.com/{file_path}`
- `https://yourstore.com/{file_path}.txt`
- `https://yourstore.com/{file_path}/llms.txt`

Where `{file_path}` is the value configured in "LLMs.txt File Path" (default: `llms`).

Example: `https://yourstore.com/llms.txt`

## Requirements

- Magento 2.4.8+
- PHP 8.1+

## Support

For issues or questions, please contact the extension developer.

## License

Proprietary


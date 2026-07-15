# Bank Logos

This directory contains SVG placeholder logos for all bank selections in the POS system.

## Generated Logos

The following bank logos have been created as SVG placeholders:

- Bank of Ceylon (BOC)
- DFCC Bank PLC
- Sampath Bank
- HSBC
- NDB Bank
- Commercial Bank
- Hatton National Bank
- Seylan Bank
- Peoples Bank
- Nations Trust Bank
- Standard Chartered Bank
- And 40+ more banks...

## How to Generate All Logos

Run the Artisan command to generate SVG placeholders for all missing banks:

```bash
php artisan logos:generate
```

This will:
- Create SVG placeholder logos for all banks
- Use unique colors for each bank
- Display bank initials in the logo
- Skip logos that already exist

## Replacing with Actual Logos

To use actual bank logos instead of placeholders:

1. Place your logo files in this directory with the exact filenames as listed in the mapping
2. You can use JPG, PNG, or SVG formats
3. Update the `getBankLogoName()` function in `resources/js/Pages/Pos/Index.vue` if needed

### Supported Filename Format

- `bank_name_logo.svg` or `bank_name_logo.jpg`
- Example: `hsbc_logo.svg`, `commercial_bank_logo.jpg`

## Logo Mapping

The logo mapping is defined in:
- `resources/js/Pages/Pos/Index.vue` → `getBankLogoName()` function (line 1944)

All 40+ banks are already mapped and ready to use!

## Current Logo List

```
alliance_finance_logo.svg
amana_bank_logo.svg
american_express_bank_logo.svg
asia_asset_finance_logo.svg
boc_bank_logo_001.svg (Bank of Ceylon) ✓
bank_of_china_logo.svg
cdb_logo.svg
cargils_bank_logo.svg
central_bank_logo.svg
central_finance_logo.svg
city_bank_logo.svg
commercial_bank_logo.svg ✓
commercial_credit_logo.svg
cooperative_rural_bank_logo.svg
dfcc_bank_logo.svg ✓
deutsche_bank_logo.svg
dialog_finance_logo.svg
fintrex_finance_logo.svg
hdfc_bank_logo.svg
hnb_finance_logo.svg
hsbc_logo.svg ✓
hatton_national_bank_logo.svg ✓
indian_bank_logo.svg
indian_overseas_bank_logo.svg
kanrich_finance_logo.svg
lb_finance_logo.svg
lolc_dev_finance_logo.svg
lolc_finance_logo.svg
lanka_credit_business_finance_logo.svg
mbsl_logo.svg
mcb_logo.svg
mercantile_investment_logo.svg
ndb_bank_logo.svg ✓
nsb_logo.svg
nations_trust_bank_logo.svg ✓
peoples_leasing_finance_logo.svg
pan_asia_bank_logo.svg
peoples_bank_logo.svg ✓
public_bank_logo.svg
rdb_logo.svg
richard_pieris_finance_logo.svg
sdb_logo.svg
senkadagala_finance_logo.svg
smib_logo.svg
sampath_bank_logo.svg ✓
sarvodaya_dev_finance_logo.svg
seylan_bank_logo.svg ✓
singer_finance_logo.svg
siyapatha_finance_logo.svg
softlogic_finance_logo.svg
standard_chartered_bank_logo.svg ✓
state_bank_india_logo.svg
union_bank_logo.svg
bank_default_logo.svg ✓

✓ = Already created
```

## Logo Display Features

✓ Responsive sizing (W: 30px, H: 30px in the modal)
✓ Fallback to default logo if image not found
✓ Proper MIME type handling for SVG files
✓ Automatic bank initials display in placeholders
✓ Color-coded by bank name for easy distinction

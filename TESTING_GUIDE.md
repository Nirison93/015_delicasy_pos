# Payment Validation Testing Guide

## How to Test the Payment Validation Fixes

### Prerequisites
- Laravel dev server running on `http://localhost:8000`
- Vite dev server running (for Vue hot-reload)
- Browser access to the POS application

### Step 1: Access the POS Page
1. Open browser and navigate to `http://localhost:8000/pos`
2. Log in if required (test user: delicasy@gmail.com)

### Step 2: Add Products to Cart
1. Select products from the left panel
2. Add quantities as needed
3. Click "Pay Now" button to open the Order Summary modal

### Step 3: Test Cash Payment Validation

#### Test 3.1: Empty Amount
- **Action**: Leave "Enter Amount" field empty
- **Expected**: 
  - Error message: "Please enter a valid payment amount."
  - "Confirm & Submit" button should be disabled (grayed out)

#### Test 3.2: Zero Amount
- **Action**: Enter "0" in the "Enter Amount" field
- **Expected**:
  - Error message: "Please enter a valid payment amount."
  - "Confirm & Submit" button should be disabled

#### Test 3.3: Amount Less Than Total
- **Action**: 
  - Total Amount = 200 LKR
  - Enter Amount = 100
- **Expected**:
  - Error message: "Entered amount cannot be less than the total amount."
  - "Confirm & Submit" button should be disabled

#### Test 3.4: Amount Equal to Total
- **Action**:
  - Total Amount = 200 LKR
  - Enter Amount = 200
- **Expected**:
  - No error message
  - "Confirm & Submit" button should be enabled (green, clickable)
  - Balance = 0

#### Test 3.5: Amount Greater Than Total
- **Action**:
  - Total Amount = 200 LKR
  - Enter Amount = 500
- **Expected**:
  - No error message
  - "Confirm & Submit" button should be enabled
  - Balance = 300 (calculated as 500 - 200)

#### Test 3.6: Quick Amount Buttons
- **Action**: Click one of the quick buttons (500, 1000, 2000, 5000)
- **Expected**:
  - The "Enter Amount" field should update to the selected amount
  - If amount >= total, "Confirm & Submit" button should enable immediately
  - Validation message should update in real-time

### Step 4: Test Card Payment Validation

#### Test 4.1: No Bank Selected
- **Action**: 
  - Switch to "Card" payment method
  - Do NOT select a bank
- **Expected**:
  - Error message: "Please select a bank."
  - "Confirm & Submit" button should be disabled

#### Test 4.2: Bank Selected
- **Action**:
  - Switch to "Card" payment method
  - Select a bank (e.g., "Bank of Ceylon")
- **Expected**:
  - No error message
  - "Confirm & Submit" button should be enabled

### Step 5: Submit Order
- **Action**: When all validations pass, click "Confirm & Submit"
- **Expected**:
  - Order should be submitted successfully
  - Should see success message or receipt
  - Order data should be saved to database

## Debugging Tips

### If Button Doesn't Respond:
1. Check browser console (F12) for JavaScript errors
2. Verify Vite dev server is running
3. Check that `paymentValidation` computed property exists in Index.vue

### If Validation Message Doesn't Show:
1. Verify the message is not empty (check browser DevTools)
2. Ensure `paymentValidation.message` contains text
3. Check that the div with `v-if="paymentValidation.message"` is visible

### If Quick Buttons Don't Work:
1. Check that `@click="() => { selectedTable.cash = amount; }"` is correct
2. Verify Vue reactivity is working (try typing in the field manually)
3. Check browser console for errors

## Expected Behavior Summary

| Scenario | Cash Payment | Card Payment |
|----------|--------------|--------------|
| Empty/Zero Amount | ❌ Disabled + Error | N/A |
| Amount < Total | ❌ Disabled + Error | N/A |
| Amount >= Total | ✅ Enabled + No Error | N/A |
| No Bank Selected | N/A | ❌ Disabled + Error |
| Bank Selected | N/A | ✅ Enabled + No Error |
| Quick Buttons | ✅ Immediate validation | N/A |

## Validation Messages Reference

### Cash Payment
- **"Please enter a valid payment amount."**
  - Shown when: Amount is empty, zero, negative, or NaN
  
- **"Entered amount cannot be less than the total amount."**
  - Shown when: Amount < Total Amount

### Card Payment
- **"Please select a bank."**
  - Shown when: No bank is selected

## Success Criteria
- ✅ All error messages appear correctly
- ✅ Button disables/enables based on validation state
- ✅ Quick buttons trigger immediate re-validation
- ✅ Order can only be submitted when validation passes
- ✅ No UI/design changes (validation is transparent)
- ✅ Change amount is calculated correctly when submitted

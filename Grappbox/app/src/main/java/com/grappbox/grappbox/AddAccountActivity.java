package com.grappbox.grappbox;

import android.accounts.AccountAuthenticatorResponse;
import android.accounts.AccountManager;
import android.content.Intent;
import android.os.Bundle;
import android.support.v7.app.AppCompatActivity;

public class AddAccountActivity extends AppCompatActivity {
    private Bundle mResponse = null;
    private AccountAuthenticatorResponse mAccountAuthenticatorResponse = null;
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_add_account);

        final Intent process = getIntent();
        if (process == null)
            throw new IllegalArgumentException();
        mAccountAuthenticatorResponse = process.getParcelableExtra(AccountManager.KEY_ACCOUNT_MANAGER_RESPONSE);
        if (mAccountAuthenticatorResponse == null)
            finish();
        mAccountAuthenticatorResponse.onRequestContinued();
    }

    public void setResponse(Bundle response) { mResponse = response; }

    @Override
    public void finish() {
        if (mAccountAuthenticatorResponse != null) {
            if (mResponse != null) {
                mAccountAuthenticatorResponse.onResult(mResponse);
            } else {
                mAccountAuthenticatorResponse.onError(AccountManager.ERROR_CODE_CANCELED, getString(R.string.error_operation_canceled));
            }
            mAccountAuthenticatorResponse = null;
        }
        super.finish();
    }
}

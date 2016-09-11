package com.grappbox.grappbox;

import android.accounts.AccountManager;
import android.os.Bundle;
import android.support.v7.app.AppCompatActivity;
import android.support.v7.widget.Toolbar;
import android.util.Log;

public class ChooseProjectActivity extends AppCompatActivity {
    private static final String LOG_TAG = ChooseProjectActivity.class.getSimpleName();
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        AccountManager am = AccountManager.get(this);

        if (am.getAccountsByType(getString(R.string.sync_account_type)).length <= 0){
            am.addAccount(getString(R.string.sync_account_type), null, null, null, this, null, null);
        }
        Log.d(LOG_TAG, "onCreate called");
        setContentView(R.layout.activity_choose_project);
        Toolbar toolbar = (Toolbar) findViewById(R.id.toolbar);
        setSupportActionBar(toolbar);
        if (getSupportActionBar() != null){
            getSupportActionBar().setElevation(0.f);
        }

    }

}

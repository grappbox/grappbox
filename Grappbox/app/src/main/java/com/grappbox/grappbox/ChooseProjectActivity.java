package com.grappbox.grappbox;

import android.Manifest;
import android.accounts.AccountManager;
import android.content.Intent;
import android.content.pm.PackageManager;
import android.os.Bundle;
import android.support.annotation.NonNull;
import android.support.v4.app.ActivityCompat;
import android.support.v7.app.AppCompatActivity;
import android.support.v7.widget.Toolbar;
import android.util.Log;

public class ChooseProjectActivity extends AppCompatActivity {
    private static final String LOG_TAG = ChooseProjectActivity.class.getSimpleName();
    private static final int PERMISSION_REQUEST_GET_ACCOUNT = 0;

    private boolean isAttached = false;
    private Bundle mCurrentInstanceState;

    @Override
    public void onRequestPermissionsResult(int requestCode, @NonNull String[] permissions, @NonNull int[] grantResults) {
        super.onRequestPermissionsResult(requestCode, permissions, grantResults);
        if (requestCode == PERMISSION_REQUEST_GET_ACCOUNT){
            if (grantResults[0] != PackageManager.PERMISSION_GRANTED)
            {
                Intent criticalErrorRedirection = new Intent(this, CriticalErrorActivity.class);
                startActivity(criticalErrorRedirection);
                return;
            }
            onCreate(mCurrentInstanceState);
        }
    }

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        if (!isAttached){
            super.onCreate(savedInstanceState);
            isAttached = true;
        }
        mCurrentInstanceState = savedInstanceState;
        AccountManager am = AccountManager.get(this);

        if (ActivityCompat.checkSelfPermission(this, Manifest.permission.GET_ACCOUNTS) != PackageManager.PERMISSION_GRANTED) {
            ActivityCompat.requestPermissions(this, new String[]{
                    Manifest.permission.GET_ACCOUNTS
            }, PERMISSION_REQUEST_GET_ACCOUNT);
            return;
        }

        if (am.getAccountsByType(getString(R.string.sync_account_type)).length <= 0) {
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

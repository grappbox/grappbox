package com.grappbox.grappbox;

import android.accounts.AccountManager;
import android.content.Intent;
import android.os.Bundle;
import android.support.v7.app.AppCompatActivity;
import android.support.v7.widget.Toolbar;

import com.grappbox.grappbox.sync.GrappboxAuthenticator;

public class ChooseProjectActivity extends AppCompatActivity {

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        AccountManager am = AccountManager.get(this);

        if (am.getAccountsByType(getString(R.string.sync_account_type)).length <= 0)
            am.addAccount(getString(R.string.sync_account_type), null, null, null, this, null, null);
        setContentView(R.layout.activity_choose_project);
        Toolbar toolbar = (Toolbar) findViewById(R.id.toolbar);
        setSupportActionBar(toolbar);

    }

}

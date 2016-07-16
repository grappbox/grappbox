package com.grappbox.grappbox.grappbox.BugTracker;

import android.content.Intent;
import android.os.Bundle;
import android.support.v4.app.FragmentManager;
import android.support.v4.app.FragmentTransaction;
import android.support.v4.app.Fragment;
import android.support.v7.app.ActionBar;
import android.support.v7.app.AppCompatActivity;
import android.support.v7.widget.Toolbar;
import android.util.Log;
import android.view.MenuItem;
import android.view.View;

import com.grappbox.grappbox.grappbox.R;

import org.json.JSONException;
import org.json.JSONObject;

public class EditBugActivity extends AppCompatActivity {

    private static String _bugId;
    private FragmentTransaction _transactions;
    private FragmentManager _fragmentManager;

    public void SetBug(String bugID)
    {
        _bugId = bugID;
    }

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        EditBugActivity me = this;
        Intent intent = getIntent();

        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_edit_bug);
        Toolbar toolbar = (Toolbar) findViewById(R.id.toolbar);

        setSupportActionBar(toolbar);
        _fragmentManager = getSupportFragmentManager();

        getSupportActionBar().setDisplayHomeAsUpEnabled(true);
        if (intent == null)
        {
            onBackPressed();
            return;
        }
        String bugId = intent.getStringExtra(BugEntity.EXTRA_GRAPPBOX_BUG_ID);
        if (bugId == null || bugId.isEmpty())
        {
            onBackPressed();
            return;
        }
        SetBug(bugId);
        if (savedInstanceState == null)
            _fragmentManager.beginTransaction().replace(R.id.fragment_container, new EditBugActivityFragment()).commit();
    }

    @Override
    public boolean onOptionsItemSelected(MenuItem item) {
        if (item.getItemId() == android.R.id.home)
        {
            onBackPressed();
            return true;
        }
        return super.onOptionsItemSelected(item);
    }

    public String GetModelId()
    {
        return _bugId;
    }
}

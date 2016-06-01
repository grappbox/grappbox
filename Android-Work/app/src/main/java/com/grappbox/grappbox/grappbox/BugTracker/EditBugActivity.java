package com.grappbox.grappbox.grappbox.BugTracker;

import android.content.Intent;
import android.os.Bundle;
import android.support.v4.app.FragmentManager;
import android.support.v4.app.FragmentTransaction;
import android.support.v4.app.Fragment;
import android.support.v7.app.AppCompatActivity;
import android.support.v7.widget.Toolbar;
import android.util.Log;

import com.grappbox.grappbox.grappbox.R;

import org.json.JSONException;
import org.json.JSONObject;

public class EditBugActivity extends AppCompatActivity {

    private static BugEntity _bug;
    private FragmentTransaction _transactions;
    private FragmentManager _fragmentManager;

    public void SetBug(BugEntity bug)
    {
        _bug = bug;
    }

    public void RefreshBug()
    {

        GetTicketTask task = new GetTicketTask(this, new OnTaskListener() {
            @Override
            public void OnTaskEnd(boolean isErrorOccured, String... params) {
                if (params.length < 1)
                    return;
                try {
                    JSONObject data = new JSONObject(params[0]);
                    _bug = new BugEntity(data);
                } catch (JSONException e) {
                    e.printStackTrace();
                }
            }
        });
        task.execute(_bug.GetId());
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
        if (savedInstanceState == null)
        {
            _fragmentManager.beginTransaction().replace(R.id.fragment_container, new EditBugActivityFragment()).commit();
        }
        String bugId = intent.getStringExtra(BugEntity.EXTRA_GRAPPBOX_BUG_ID);


        if (bugId == null || bugId.isEmpty())
            onBackPressed();
        GetTicketTask task = new GetTicketTask(this, new OnTaskListener() {
            @Override
            public void OnTaskEnd(boolean isErrorOccured, String... params) {

                if (isErrorOccured || params.length < 1) {
                    onBackPressed();
                    return;
                }
                try {
                    JSONObject data = new JSONObject(params[0]);
                    _bug = new BugEntity(data);
                    toolbar.setTitle("Edit " + _bug.GetTitle());
                    for (Fragment frag : getSupportFragmentManager().getFragments())
                    {
                        if (frag instanceof EditBugActivityFragment)
                            ((EditBugActivityFragment)frag).SetBugEntity(_bug);
                    }
                } catch (JSONException e) {
                    e.printStackTrace();
                }

            }
        });
        task.execute(bugId);
    }

    public BugEntity GetModel()
    {
        return _bug;
    }
}

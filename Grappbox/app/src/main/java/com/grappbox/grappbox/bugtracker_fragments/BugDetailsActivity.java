package com.grappbox.grappbox.bugtracker_fragments;

import android.content.Intent;
import android.graphics.drawable.ColorDrawable;
import android.os.Build;
import android.os.Bundle;
import android.support.v4.content.ContextCompat;
import android.support.v7.app.AppCompatActivity;
import android.support.v7.widget.Toolbar;
import android.util.Log;
import android.view.Menu;
import android.view.MenuItem;

import com.grappbox.grappbox.R;
import com.grappbox.grappbox.Utils;
import com.grappbox.grappbox.model.BugModel;
import com.grappbox.grappbox.sync.GrappboxJustInTimeService;

public class BugDetailsActivity extends AppCompatActivity {
    public static final String EXTRA_BUG_MODEL = "com.grappbox.grappbox.bugtracker_fragments.EXTRA_BUG_MODEL";
    private BugModel mData;
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_bug_details);
        Toolbar toolbar = (Toolbar) findViewById(R.id.toolbar);
        setSupportActionBar(toolbar);
        getSupportActionBar().setBackgroundDrawable(new ColorDrawable(ContextCompat.getColor(this, R.color.GrappPurple)));
        getSupportActionBar().setDisplayHomeAsUpEnabled(true);
        getSupportActionBar().setDisplayShowHomeEnabled(true);
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.LOLLIPOP) {
            getWindow().setStatusBarColor(Utils.Color.getThemeAccentColor(this));
        }
        mData = getIntent().getParcelableExtra(EXTRA_BUG_MODEL);
        getSupportActionBar().setTitle(mData.title);
    }

    public BugModel getBugModel(){
        return mData;
    }

    @Override
    public void setTitle(CharSequence title) {
        super.setTitle(title);
        getSupportActionBar().setTitle(title);
    }

    @Override
    public boolean onCreateOptionsMenu(Menu menu) {
        getMenuInflater().inflate(mData.isClosed ? R.menu.bug_detail_menu_close : R.menu.bug_detail_menu_open, menu);
        return true;
    }

    private void actionEdit(){
        Intent edit = new Intent(this, NewBugActivity.class);
        edit.setAction(NewBugActivity.ACTION_EDIT);
        edit.putExtra(NewBugActivity.EXTRA_MODEL, getIntent().getParcelableExtra(EXTRA_BUG_MODEL));
        startActivity(edit);
    }

    private void actionDelete(){
        if (mData.isClosed){
            Intent open = new Intent(this, GrappboxJustInTimeService.class);
            open.setAction(GrappboxJustInTimeService.ACTION_REOPEN_BUG);
            open.putExtra(GrappboxJustInTimeService.EXTRA_BUG_ID, mData._id);
            startService(open);
            mData.isClosed = false;
        } else {
            Intent close = new Intent(this, GrappboxJustInTimeService.class);
            close.setAction(GrappboxJustInTimeService.ACTION_CLOSE_BUG);
            close.putExtra(GrappboxJustInTimeService.EXTRA_BUG_ID, mData._id);
            startService(close);
            Log.d("del", "actionDelete");
            mData.isClosed = true;
        }
        invalidateOptionsMenu();
    }

    @Override
    public boolean onOptionsItemSelected(MenuItem item) {
        switch (item.getItemId()){
            case android.R.id.home:
                onBackPressed();
                return true;
            case R.id.action_edit:
                actionEdit();
                return true;
            case R.id.action_delete:
                actionDelete();
                return true;
        }
        return super.onOptionsItemSelected(item);
    }
}

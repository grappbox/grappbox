package com.grappbox.grappbox.bugtracker_fragments;

import android.content.DialogInterface;
import android.content.Intent;
import android.graphics.drawable.ColorDrawable;
import android.os.Build;
import android.os.Bundle;
import android.os.Handler;
import android.support.v4.app.FragmentActivity;
import android.support.v4.content.ContextCompat;
import android.support.v7.app.AlertDialog;
import android.support.v7.app.AppCompatActivity;
import android.support.v7.widget.Toolbar;
import android.view.Menu;
import android.view.MenuItem;
import android.widget.TextView;

import com.grappbox.grappbox.R;
import com.grappbox.grappbox.model.BugModel;
import com.grappbox.grappbox.receiver.BugReceiver;
import com.grappbox.grappbox.sync.BugtrackerJIT;
import com.grappbox.grappbox.sync.GrappboxJustInTimeService;

import org.w3c.dom.Text;

import java.util.ArrayList;
import java.util.List;

public class NewBugActivity extends AppCompatActivity {
    public static final String ACTION_EDIT = "com.grappbox.grappbox.bugtracker_fragements.ACTION_EDIT";
    public static final String ACTION_NEW = "com.grappbox.grappbox.bugtracker_fragements.ACTION_NEW";

    public static final String EXTRA_MODEL = "com.grappbox.grappbox.bugtracker_fragments.EXTRA_MODEL";
    public static final String EXTRA_PROJECT_ID = "com.grappbox.grappbox.bugtracker_fragments.EXTRA_PROJECT_ID";
    private boolean mIsEditMode;
    private BugModel mModel = null;
    private long mProjectID = -1;
    private List<ActivityActions> callback;
    private TextView mTitle, mDescription;
    private BugReceiver receiver;

    public interface ActivityActions{
        void onSave();
    }

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        callback = new ArrayList<>();
        setTheme(R.style.BugtrackerEditTheme);
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.LOLLIPOP) {
            getWindow().setStatusBarColor(ContextCompat.getColor(this, R.color.GrappPurple));
        }
        setContentView(R.layout.activity_new_bug);
        Toolbar toolbar = (Toolbar) findViewById(R.id.toolbar);
        setSupportActionBar(toolbar);
        getSupportActionBar().setDisplayHomeAsUpEnabled(true);
        getSupportActionBar().setBackgroundDrawable(new ColorDrawable(ContextCompat.getColor(this, R.color.GrappPurple)));
        getSupportActionBar().setHomeAsUpIndicator(ContextCompat.getDrawable(this, R.drawable.ic_cross));
        mIsEditMode = getIntent().getAction().equals(ACTION_EDIT);
        mTitle = (TextView) findViewById(R.id.title);
        mDescription = (TextView) findViewById(R.id.description);

        if (mIsEditMode){
            mModel = getIntent().getParcelableExtra(EXTRA_MODEL);
            mTitle.setText(mModel.title);
            mDescription.setText(mModel.desc);
        }
        else
            mProjectID = getIntent().getLongExtra(EXTRA_PROJECT_ID, -1);
    }

    public void registerActivityActionCallback(BugReceiver.Callback action){
        if (receiver == null)
            receiver = new BugReceiver(this, new Handler());
        receiver.registerCallback(action);
    }

    @Override
    public boolean onCreateOptionsMenu(Menu menu) {
        getMenuInflater().inflate(R.menu.bug_new_menu, menu);
        return true;
    }

    private void actionCancel(){
        AlertDialog.Builder buidler = new AlertDialog.Builder(this, R.style.BugtrackerDialogOverride);

        buidler.setTitle(R.string.dialog_cancel_without_saving_title);
        buidler.setMessage(R.string.dialog_cancel_without_saving);
        buidler.setPositiveButton(R.string.quit_word, new DialogInterface.OnClickListener() {
            @Override
            public void onClick(DialogInterface dialog, int which) {
                onBackPressed();
                dialog.dismiss();
            }
        });
        buidler.setNegativeButton(R.string.negative_response, new DialogInterface.OnClickListener() {
            @Override
            public void onClick(DialogInterface dialog, int which) {
                dialog.dismiss();
            }
        });
        buidler.show();
    }

    private void actionSave(){
        Intent save = new Intent(this, BugtrackerJIT.class);
        save.setAction(mIsEditMode ? BugtrackerJIT.ACTION_EDIT_BUG : BugtrackerJIT.ACTION_CREATE_BUG);
        save.putExtra(mIsEditMode ? BugtrackerJIT.EXTRA_BUG_ID : GrappboxJustInTimeService.EXTRA_PROJECT_ID, mIsEditMode ? mModel._id : mProjectID);
        save.putExtra(GrappboxJustInTimeService.EXTRA_TITLE, mTitle.getText().toString());
        save.putExtra(GrappboxJustInTimeService.EXTRA_DESCRIPTION, mDescription.getText().toString());
        save.putExtra(GrappboxJustInTimeService.EXTRA_CLIENT_ACTION, false);
        save.putExtra(GrappboxJustInTimeService.EXTRA_RESPONSE_RECEIVER, receiver);
        startService(save);
    }

    @Override
    public boolean onOptionsItemSelected(MenuItem item) {
        switch (item.getItemId()){
            case android.R.id.home:
                actionCancel();
                return true;
            case R.id.action_save:
                actionSave();
                return true;
        }
        return super.onOptionsItemSelected(item);
    }

}

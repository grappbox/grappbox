/*
 * Created by Marc Wieser on 5/11/2016
 * If you have any problem or question about this work
 * please contact the author at marc.wieser@gmail.com
 *
 * The following code is owned by GrappBox you can't
 * use it without any authorization or special instructions
 * GrappBox © 2016
 */

package com.grappbox.grappbox;

import android.os.Bundle;
import android.app.Activity;
import android.support.v7.app.AppCompatActivity;
import android.support.v7.widget.Toolbar;
import android.view.Menu;
import android.view.MenuInflater;
import android.view.MenuItem;

import com.grappbox.grappbox.model.RoleModel;

import java.util.ArrayList;
import java.util.List;

public class RoleEditActivity extends AppCompatActivity {
    public static final String ACTION_EDIT = "edit";
    public static final String ACTION_NEW = "new";

    public static final String EXTRA_MODEL = "model";

    private boolean isNew;
    private RoleModel model = null;
    private List<SaveCallback> mSaveCallback = null;

    public interface SaveCallback{
        void onSave();
        void onDelete();
    }

    public void registerSaveCallback(SaveCallback callback){
        if (mSaveCallback == null){
            mSaveCallback = new ArrayList<>();
        }
        mSaveCallback.add(callback);
    }

    public void unregisterSaveCallback(SaveCallback callback){
        if (mSaveCallback == null)
            return;
        mSaveCallback.remove(callback);
    }

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_role_edit);
        setSupportActionBar((Toolbar) findViewById(R.id.toolbar));
        getSupportActionBar().setDisplayHomeAsUpEnabled(true);
        isNew = getIntent() == null || getIntent().getAction().equals(ACTION_NEW);
        if (!isNew){
            model = getIntent().getParcelableExtra(EXTRA_MODEL);
        }
    }

    @Override
    public boolean onCreateOptionsMenu(Menu menu) {
        if (getIntent().getAction().equals(ACTION_NEW)){
            getMenuInflater().inflate(R.menu.bug_new_menu, menu);
        } else {
            getMenuInflater().inflate(R.menu.role_edit_menu, menu);
        }

        return true;
    }

    @Override
    public boolean onOptionsItemSelected(MenuItem item) {
        switch (item.getItemId()){
            case R.id.action_save:
                if (mSaveCallback == null)
                    return false;
                for (SaveCallback saveCallback : mSaveCallback) {
                    saveCallback.onSave();
                }
                onBackPressed();
                return true;
            case R.id.action_delete:
                if (mSaveCallback == null)
                    return false;
                for (SaveCallback saveCallback : mSaveCallback){
                    saveCallback.onDelete();
                }
                onBackPressed();
                return true;
        }
        return super.onOptionsItemSelected(item);
    }
}

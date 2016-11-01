package com.grappbox.grappbox.custom_preferences;

import android.app.AlertDialog;
import android.content.Context;
import android.content.DialogInterface;
import android.content.Intent;
import android.preference.DialogPreference;

import com.grappbox.grappbox.R;
import com.grappbox.grappbox.model.UserModel;
import com.grappbox.grappbox.sync.GrappboxJustInTimeService;


public class UserPreference extends DialogPreference {
    private UserModel mModel;
    private long mProjectId;
    public UserPreference(Context context, UserModel model, long projectId) {
        super(context, null);
        mModel = model;
        mProjectId = projectId;
        setTitle(mModel.mFirstname+" "+mModel.mLastname);
    }

    @Override
    protected void onPrepareDialogBuilder(AlertDialog.Builder builder) {
        super.onPrepareDialogBuilder(builder);
        builder.setNegativeButton(null, null);
        builder.setPositiveButton(null, null);
        builder.setTitle(mModel.mFirstname+" "+mModel.mLastname);
        builder.setItems(R.array.user_preference_dialog_choice_entry, new DialogInterface.OnClickListener() {
            @Override
            public void onClick(DialogInterface dialog, int position) {
                if (position == 0){
                    android.support.v7.app.AlertDialog.Builder builder = new android.support.v7.app.AlertDialog.Builder(getContext());

                    builder.setTitle("Change role by");
                    //Todo : construct roles list
                    builder.show();
                } else {
                    Intent delete = new Intent(getContext(), GrappboxJustInTimeService.class);
                    delete.setAction(GrappboxJustInTimeService.ACTION_DELETE_USER_FROM_PROJECT);
                    delete.putExtra(GrappboxJustInTimeService.EXTRA_PROJECT_ID, mProjectId);
                    delete.putExtra(GrappboxJustInTimeService.EXTRA_USER_ID, mModel._id);
                    getContext().startService(delete);
                }
                dialog.dismiss();
            }
        });
    }
}

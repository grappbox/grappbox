package com.grappbox.grappbox.custom_preferences;

import android.app.AlertDialog;
import android.content.Context;
import android.content.DialogInterface;
import android.content.Intent;
import android.database.Cursor;
import android.os.AsyncTask;
import android.preference.DialogPreference;
import android.util.Log;

import com.grappbox.grappbox.R;
import com.grappbox.grappbox.data.GrappboxContract;
import com.grappbox.grappbox.model.RoleModel;
import com.grappbox.grappbox.model.UserModel;
import com.grappbox.grappbox.sync.GrappboxJustInTimeService;


public class UserPreference extends DialogPreference {
    private UserModel mModel;
    private long mProjectId;
    private RoleModel[] existingModels = null;
    private int mCurrentRolePos = -1;

    public UserPreference(Context context, UserModel model, long projectId) {
        super(context, null);
        mModel = model;
        mProjectId = projectId;
        setTitle(mModel.mFirstname+" "+mModel.mLastname);
        new AdditionalDataLoading(getContext()).execute();
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
                    builder.setItems(RoleModel.toStringArray(existingModels), new DialogInterface.OnClickListener() {
                        @Override
                        public void onClick(DialogInterface dialog, int which) {
                            RoleModel clickedRole = existingModels[which];
                            if (which == mCurrentRolePos)
                                return;
                            Intent unassign = new Intent(getContext(), GrappboxJustInTimeService.class);
                            unassign.setAction(GrappboxJustInTimeService.ACTION_UNASSIGN_USER_ROLE);
                            unassign.putExtra(GrappboxJustInTimeService.EXTRA_PROJECT_ID, mProjectId);
                            unassign.putExtra(GrappboxJustInTimeService.EXTRA_USER_ID, mModel._id);
                            unassign.putExtra(GrappboxJustInTimeService.EXTRA_ROLE_ID, existingModels[mCurrentRolePos]._id);
                            getContext().startService(unassign);
                            mCurrentRolePos = which;
                            Intent assign = new Intent(getContext(), GrappboxJustInTimeService.class);
                            assign.setAction(GrappboxJustInTimeService.ACTION_ASSIGN_USER_ROLE);
                            assign.putExtra(GrappboxJustInTimeService.EXTRA_ROLE_ID, existingModels[mCurrentRolePos]._id);
                            assign.putExtra(GrappboxJustInTimeService.EXTRA_USER_ID, mModel._id);
                            getContext().startService(assign);
                            setSummary(existingModels[mCurrentRolePos].name);
                        }
                    });
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

    private class AdditionalDataLoading extends AsyncTask<Void, Void, RoleModel[]>{
        private Context mContext;
        private int rolePos = -1;

        AdditionalDataLoading(Context context){
            mContext = context;
        }

        @Override
        protected RoleModel[] doInBackground(Void... params) {
            Cursor data = mContext.getContentResolver().query(GrappboxContract.RolesEntry.CONTENT_URI, RoleModel.projection, GrappboxContract.ProjectEntry.TABLE_NAME + "." + GrappboxContract.ProjectEntry._ID+"=?", new String[]{String.valueOf(mProjectId)}, null);
            Cursor currentRole = mContext.getContentResolver().query(GrappboxContract.UserEntry.buildUserWithProject(), new String[]{GrappboxContract.RolesEntry.TABLE_NAME + "." + GrappboxContract.RolesEntry._ID}, GrappboxContract.UserEntry.TABLE_NAME + "." + GrappboxContract.UserEntry._ID+"=?", new String[]{String.valueOf(mModel._id)}, null);
            if (data == null || !data.moveToFirst())
                return null;
            boolean isCurrentRoleDefined = currentRole != null && currentRole.moveToFirst();
            int size = data.getCount();

            RoleModel[] models = new RoleModel[size];
            do{
                models[data.getPosition()] = new RoleModel(data);
                if (isCurrentRoleDefined && currentRole.getLong(0) == models[data.getPosition()]._id){
                    rolePos = data.getPosition();
                }
            } while(data.moveToNext());
            data.close();
            if (isCurrentRoleDefined)
                currentRole.close();
            return models;
        }

        @Override
        protected void onPostExecute(RoleModel[] roleModels) {
            super.onPostExecute(roleModels);
            existingModels = roleModels;
            if (rolePos != -1){
                mCurrentRolePos = rolePos;
                setSummary(roleModels[rolePos].name);
            }

        }
    }
}

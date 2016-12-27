package com.grappbox.grappbox.bugtracker_fragments;


import android.content.Context;
import android.content.DialogInterface;
import android.content.Intent;
import android.database.Cursor;
import android.os.AsyncTask;
import android.os.Bundle;
import android.support.annotation.Nullable;
import android.support.v4.app.ActivityCompat;
import android.support.v4.app.Fragment;
import android.support.v4.app.LoaderManager;
import android.support.v4.content.CursorLoader;
import android.support.v4.content.Loader;
import android.support.v7.app.AlertDialog;
import android.support.v7.widget.LinearLayoutManager;
import android.support.v7.widget.RecyclerView;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.TextView;

import com.grappbox.grappbox.R;
import com.grappbox.grappbox.adapter.AssigneeEditAdapter;
import com.grappbox.grappbox.adapter.TagEditAdapter;
import com.grappbox.grappbox.data.GrappboxContract;
import com.grappbox.grappbox.model.BugModel;
import com.grappbox.grappbox.model.BugTagModel;
import com.grappbox.grappbox.model.UserModel;
import com.grappbox.grappbox.receiver.BugReceiver;
import com.grappbox.grappbox.sync.BugtrackerJIT;
import com.grappbox.grappbox.sync.GrappboxJustInTimeService;

import java.util.ArrayList;
import java.util.Arrays;
import java.util.List;

/**
 * A simple {@link Fragment} subclass.
 */
public class NewBugFragment extends Fragment implements LoaderManager.LoaderCallbacks<Cursor>, BugReceiver.Callback {
    private RecyclerView mTagRecycler, mAssigneeRecycler;
    private TagEditAdapter mTagAdapter;
    private AssigneeEditAdapter mAssigneeAdapter;
    private BugModel mModel;
    private List<BugTagModel> mExistingTag;
    private List<UserModel> mExistingUser;
    private List<UserModel> mBaseUser;
    private List<BugTagModel> mCreatingTag;
    private boolean mIsEditMode = false;
    private long mProjectID = -1;

    private static int LOADER_EXISTING_TAGS = 0;
    private static int LOADER_EXISTING_USERS = 1;

    public NewBugFragment() {
        // Required empty public constructor
    }


    @Override
    public void onActivityCreated(@Nullable Bundle savedInstanceState) {
        super.onActivityCreated(savedInstanceState);
        getActivity().getSupportLoaderManager().initLoader(LOADER_EXISTING_TAGS, null, this);
        getActivity().getSupportLoaderManager().initLoader(LOADER_EXISTING_USERS, null, this);
    }

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        // Inflate the layout for this fragment
        View v = inflater.inflate(R.layout.fragment_new_bug, container, false);

        Intent intent = getActivity().getIntent();
        mTagAdapter = new TagEditAdapter(getActivity());
        mAssigneeAdapter = new AssigneeEditAdapter(getActivity());
        mTagRecycler = (RecyclerView) v.findViewById(R.id.recycler_tags);
        mCreatingTag = new ArrayList<>();

        mTagRecycler.setLayoutManager(new LinearLayoutManager(getActivity(), LinearLayoutManager.HORIZONTAL, false));
        mTagRecycler.setAdapter(mTagAdapter);
        mAssigneeRecycler = (RecyclerView) v.findViewById(R.id.recycler_assignees);
        mAssigneeRecycler.setLayoutManager(new LinearLayoutManager(getActivity(), LinearLayoutManager.HORIZONTAL, false));
        mAssigneeRecycler.setAdapter(mAssigneeAdapter);

        if (intent.getAction().equals(NewBugActivity.ACTION_EDIT)){
            mModel = intent.getParcelableExtra(NewBugActivity.EXTRA_MODEL);
            mBaseUser = mModel.assignees;
            mTagAdapter.setDataset(mModel.tags);
            mAssigneeAdapter.setDataset(mModel.assignees);
            mIsEditMode = true;
        } else {
            mModel = null;
            mBaseUser = new ArrayList<>();
            mProjectID = intent.getLongExtra(NewBugActivity.EXTRA_PROJECT_ID, -1);
        }
        if (getActivity() instanceof NewBugActivity){
            ((NewBugActivity) getActivity()).registerActivityActionCallback(this);
        }

        v.findViewById(R.id.set_tag_btn).setOnClickListener(new OnAddTag(getActivity()));
        v.findViewById(R.id.assign_btn).setOnClickListener(new OnChangeAssignee(getActivity()));
        v.findViewById(R.id.create_tag_btn).setOnClickListener(new OnCreateTag(getActivity()));
        return v;
    }

    @Override
    public void onDataReceived(BugModel model) {
        new SaveAdditionalData().execute(model);
    }

    private class OnAddTag implements View.OnClickListener {
        private Context mContext;

        public OnAddTag(Context context){
            mContext = context;
        }

        @Override
        public void onClick(View v) {

            AlertDialog.Builder builder = new AlertDialog.Builder(mContext);
            final List<BugTagModel> dataset = mTagAdapter.getDataset();
            final List<BugTagModel> diff = new ArrayList<>(mExistingTag);
            for (int i = 0; i < diff.size(); ++i){
                BugTagModel model = diff.get(i);
                for (int j = 0; j < dataset.size(); ++j){
                    BugTagModel data = dataset.get(j);
                    if (model.equals(data)){
                        diff.remove(i);
                    }
                }
            }
            for (BugTagModel model : diff){
                for (BugTagModel modelComp : dataset){
                    if (model.equals(modelComp))
                        diff.remove(model);
                }
            }
            builder.setSingleChoiceItems(BugTagModel.toStringArray(diff.toArray(new BugTagModel[diff.size()])), 0, new DialogInterface.OnClickListener() {
                @Override
                public void onClick(DialogInterface dialog, int which) {
                    dataset.add(diff.get(which));
                    mTagAdapter.setDataset(dataset);
                    dialog.dismiss();
                }
            });
            builder.setTitle(R.string.add_a_tag);
            builder.show();
        }
    }

    private class OnCreateTag implements View.OnClickListener {
        private Context mContext;

        public OnCreateTag(Context context){
            mContext = context;
        }

        @Override
        public void onClick(View v) {
            AlertDialog.Builder builder = new AlertDialog.Builder(mContext, R.style.BugtrackerDialogOverride);
            builder.setTitle(R.string.create_a_new_tag);

            builder.setView(R.layout.dialog_add_bug_tag);
            builder.setPositiveButton(R.string.positive_response, new DialogInterface.OnClickListener() {
                @Override
                public void onClick(DialogInterface dialog, int which) {
                    if (dialog instanceof AlertDialog){
                        TextView title = (TextView) ((AlertDialog) dialog).findViewById(R.id.title);
                        BugTagModel model = new BugTagModel(-1, title.getText().toString(), "#9E58DC");
                        mCreatingTag.add(model);
                        mTagAdapter.addTag(model);
                    }
                }
            });
            builder.setNegativeButton(R.string.negative_response, new DialogInterface.OnClickListener() {
                @Override
                public void onClick(DialogInterface dialog, int which) {
                    dialog.cancel();
                }
            });
            builder.show();
        }
    }

    private class OnChangeAssignee implements View.OnClickListener {
        private Context mContext;

        public OnChangeAssignee(Context context){
            mContext = context;
        }

        @Override
        public void onClick(View v) {
            AlertDialog.Builder builder = new AlertDialog.Builder(mContext, R.style.BugtrackerDialogOverride);
            builder.setTitle(R.string.edit_assignee);

            boolean[] checkedUsers = new boolean[mExistingUser.size()];
            final List<UserModel> dataset = mAssigneeAdapter.getDataset();
            final List<UserModel> selected = new ArrayList<>();

            for (int i = 0; i < checkedUsers.length; i++){
                UserModel model = mExistingUser.get(i);
                checkedUsers[i] = false;
                for (int j = 0; j < dataset.size(); j++){
                    if (model.equals(dataset.get(j))){
                        checkedUsers[i] = true;
                        selected.add(model);
                        break;
                    }
                }
            }
            Log.d("BugAss", Arrays.toString(checkedUsers));
            builder.setMultiChoiceItems(UserModel.toStringArray(mExistingUser.toArray(new UserModel[mExistingUser.size()])), checkedUsers, new DialogInterface.OnMultiChoiceClickListener() {
                @Override
                public void onClick(DialogInterface dialog, int which, boolean isChecked) {
                    UserModel model = mExistingUser.get(which);
                    if (isChecked){
                        selected.add(model);
                    } else {
                        selected.remove(model);
                    }
                }
            });
            builder.setPositiveButton(R.string.positive_response, new DialogInterface.OnClickListener() {
                @Override
                public void onClick(DialogInterface dialog, int which) {
                    mAssigneeAdapter.setDataset(selected);
                }
            });
            builder.setNegativeButton(R.string.negative_response, new DialogInterface.OnClickListener() {
                @Override
                public void onClick(DialogInterface dialog, int which) {
                    dialog.cancel();
                }
            });
            builder.show();
        }
    }

    @Override
    public Loader<Cursor> onCreateLoader(int id, Bundle args) {
        if (id == LOADER_EXISTING_TAGS){
            final String[] projection = {
                    GrappboxContract.BugtrackerTagEntry.TABLE_NAME + "." + GrappboxContract.BugtrackerTagEntry._ID,
                    GrappboxContract.BugtrackerTagEntry.TABLE_NAME + "." + GrappboxContract.BugtrackerTagEntry.COLUMN_NAME
            };
            final String selection = GrappboxContract.BugtrackerTagEntry.COLUMN_LOCAL_PROJECT_ID+"=?";
            final String[] arg = {
                    String.valueOf(mIsEditMode ? mModel.projectID  : mProjectID)
            };
            return new CursorLoader(getActivity(), GrappboxContract.BugtrackerTagEntry.CONTENT_URI, projection, selection, arg, null);
        }
        else if (id == LOADER_EXISTING_USERS){
            final String[] projection = {
                    GrappboxContract.UserEntry.TABLE_NAME + "." + GrappboxContract.UserEntry._ID,
                    GrappboxContract.UserEntry.TABLE_NAME + "." + GrappboxContract.UserEntry.COLUMN_FIRSTNAME,
                    GrappboxContract.UserEntry.TABLE_NAME + "." + GrappboxContract.UserEntry.COLUMN_LASTNAME,
                    GrappboxContract.UserEntry.TABLE_NAME + "." + GrappboxContract.UserEntry.COLUMN_DATE_BIRTHDAY_UTC,
                    GrappboxContract.UserEntry.TABLE_NAME + "." + GrappboxContract.UserEntry.COLUMN_CONTACT_EMAIL,
                    GrappboxContract.UserEntry.TABLE_NAME + "." + GrappboxContract.UserEntry.COLUMN_URI_AVATAR
            };
            final String selection = GrappboxContract.ProjectEntry.TABLE_NAME + "." + GrappboxContract.ProjectEntry._ID+"=?";
            final String[] arg = {
                    String.valueOf(mIsEditMode ? mModel.projectID : mProjectID)
            };
            return new CursorLoader(getActivity(), GrappboxContract.UserEntry.buildUserWithProject(), projection, selection, arg, null);
        }
        return null;
    }

    @Override
    public void onLoadFinished(Loader<Cursor> loader, Cursor data) {
        if (mExistingUser == null)
            mExistingUser = new ArrayList<>();
        if (mExistingTag == null)
            mExistingTag = new ArrayList<>();
        if (loader.getId() == LOADER_EXISTING_TAGS && data.moveToFirst()){
            mExistingTag.clear();
            do{
                mExistingTag.add(new BugTagModel(data));
            } while (data.moveToNext());
        } else if (loader.getId() == LOADER_EXISTING_USERS && data.moveToFirst()){

            mExistingUser.clear();
            do {
                mExistingUser.add(new UserModel(data));
            } while (data.moveToNext());
        }
    }

    @Override
    public void onLoaderReset(Loader<Cursor> loader) {

    }

    class SaveAdditionalData extends AsyncTask<BugModel, Void, Void>{

        @Override
        protected Void doInBackground(BugModel... params) {
            BugModel model = params[0];
            List<BugTagModel> tags = mTagAdapter.getDataset();
            List<BugTagModel> delTags = mTagAdapter.getDeletedTags();

            List<UserModel> assignees = mAssigneeAdapter.getDataset();

            for (BugTagModel tag : delTags){
                Intent apiTag = new Intent(getActivity(), BugtrackerJIT.class);
                apiTag.setAction(BugtrackerJIT.ACTION_REMOVE_BUGTAG);
                apiTag.putExtra(BugtrackerJIT.EXTRA_BUG_ID, model._id);
                apiTag.putExtra(GrappboxJustInTimeService.EXTRA_TAG_ID, tag._id);
                getActivity().startService(apiTag);
            }

            for (BugTagModel tag : tags){
                Intent apiTag = new Intent(getActivity(), BugtrackerJIT.class);
                if (mCreatingTag.contains(tag)){
                    apiTag.setAction(BugtrackerJIT.ACTION_CREATE_TAG);
                    apiTag.putExtra(GrappboxJustInTimeService.EXTRA_PROJECT_ID, model.projectID);
                    apiTag.putExtra(BugtrackerJIT.EXTRA_BUG_ID, model._id);
                    apiTag.putExtra(GrappboxJustInTimeService.EXTRA_TITLE, tag.name);
                } else {
                    apiTag.setAction(BugtrackerJIT.ACTION_EDIT_BUGTAG);
                    apiTag.putExtra(BugtrackerJIT.EXTRA_BUG_ID, model._id);
                    apiTag.putExtra(GrappboxJustInTimeService.EXTRA_TAG_ID, tag._id);
                }
                getActivity().startService(apiTag);
            }
            ArrayList<Long> assignAdd = new ArrayList<>();
            ArrayList<Long> assignDel = new ArrayList<>();
            for (UserModel umodel : mExistingUser){
                boolean contain = false;
                for (UserModel uBaseModel : mBaseUser){
                    if (uBaseModel.equals(umodel))
                    {
                        contain = true;
                        break;
                    }
                }
                if (contain && !assignees.contains(umodel)){
                    assignDel.add(umodel._id);
                }
                else if (!contain && assignees.contains(umodel)){
                    assignAdd.add(umodel._id);
                }
            }
            Intent apiUser = new Intent(getActivity(), BugtrackerJIT.class);
            Bundle apiArgs = new Bundle();
            Log.e("Test", String.valueOf(assignDel));
            apiArgs.putSerializable(GrappboxJustInTimeService.EXTRA_DEL_PARTICIPANT, assignDel);
            apiArgs.putSerializable(GrappboxJustInTimeService.EXTRA_ADD_PARTICIPANT, assignAdd);
            apiUser.setAction(BugtrackerJIT.ACTION_SET_PARTICIPANT);
            apiUser.putExtra(GrappboxJustInTimeService.EXTRA_BUNDLE, apiArgs);
            apiUser.putExtra(BugtrackerJIT.EXTRA_BUG_ID, model._id);
            getActivity().startService(apiUser);
            return null;
        }

        @Override
        protected void onPostExecute(Void aVoid) {
            super.onPostExecute(aVoid);
            getActivity().onBackPressed();
        }
    }
}

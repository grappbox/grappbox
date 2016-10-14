package com.grappbox.grappbox.bugtracker_fragments;


import android.content.Intent;
import android.database.Cursor;
import android.os.Bundle;
import android.support.annotation.Nullable;
import android.support.v4.app.Fragment;
import android.support.v4.app.LoaderManager;
import android.support.v4.content.CursorLoader;
import android.support.v4.content.Loader;
import android.support.v7.widget.GridLayoutManager;
import android.support.v7.widget.RecyclerView;
import android.util.AttributeSet;
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
import com.grappbox.grappbox.sync.GrappboxJustInTimeService;

import org.w3c.dom.Text;

import java.util.ArrayList;
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
        mTagRecycler.setLayoutManager(new GridLayoutManager(getActivity(), GridLayoutManager.DEFAULT_SPAN_COUNT));
        //mTagRecycler.setAdapter(mTagAdapter);
        mAssigneeRecycler = (RecyclerView) v.findViewById(R.id.recycler_assignees);
        mAssigneeRecycler.setLayoutManager(new GridLayoutManager(getActivity(), GridLayoutManager.DEFAULT_SPAN_COUNT));
        //mAssigneeRecycler.setAdapter(mAssigneeAdapter);

        if (intent.getAction().equals(NewBugActivity.ACTION_EDIT)){
            mModel = intent.getParcelableExtra(NewBugActivity.EXTRA_MODEL);
            mTagAdapter.setDataset(mModel.tags);
            mAssigneeAdapter.setDataset(mModel.assignees);
            mIsEditMode = true;
        } else {
            mModel = null;
            mProjectID = intent.getLongExtra(NewBugActivity.EXTRA_PROJECT_ID, -1);
        }
        if (getActivity() instanceof NewBugActivity){
            ((NewBugActivity) getActivity()).registerActivityActionCallback(this);
        }

        v.findViewById(R.id.set_tag_btn).setOnClickListener(new OnAddTag());
        v.findViewById(R.id.assign_btn).setOnClickListener(new OnChangeAssignee());
        v.findViewById(R.id.create_tag_btn).setOnClickListener(new OnCreateTag());
        return v;
    }

    @Override
    public void onDataReceived(BugModel model) {
        getActivity().onBackPressed();
    }

    private static class OnAddTag implements View.OnClickListener {

        @Override
        public void onClick(View v) {

        }
    }

    private static class OnCreateTag implements View.OnClickListener {

        @Override
        public void onClick(View v) {

        }
    }

    private static class OnChangeAssignee implements View.OnClickListener {

        @Override
        public void onClick(View v) {

        }
    }

    @Override
    public Loader<Cursor> onCreateLoader(int id, Bundle args) {
        if (id == LOADER_EXISTING_TAGS){
            final String[] projection = {
                    GrappboxContract.TagEntry.TABLE_NAME + "." + GrappboxContract.TagEntry._ID,
                    GrappboxContract.TagEntry.TABLE_NAME + "." + GrappboxContract.TagEntry.COLUMN_NAME
            };
            final String selection = GrappboxContract.TagEntry.COLUMN_LOCAL_PROJECT_ID+"=?";
            final String[] arg = {
                    String.valueOf(mIsEditMode ? mModel.projectID  : mProjectID)
            };
            return new CursorLoader(getActivity(), GrappboxContract.TagEntry.CONTENT_URI, projection, selection, arg, null);
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
        mExistingTag = new ArrayList<>();
        mExistingUser = new ArrayList<>();
        if (loader.getId() == LOADER_EXISTING_TAGS && data.moveToFirst()){
            do{
                mExistingTag.add(new BugTagModel(data));
            } while (data.moveToNext());
        } else if (loader.getId() == LOADER_EXISTING_USERS && data.moveToFirst()){
            do {
                mExistingUser.add(new UserModel(data));
            } while (data.moveToNext());
        }
    }

    @Override
    public void onLoaderReset(Loader<Cursor> loader) {

    }
}

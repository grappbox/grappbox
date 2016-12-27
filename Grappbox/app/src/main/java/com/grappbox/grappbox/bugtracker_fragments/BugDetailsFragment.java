package com.grappbox.grappbox.bugtracker_fragments;


import android.content.Intent;
import android.database.Cursor;
import android.os.AsyncTask;
import android.os.Bundle;
import android.support.annotation.Nullable;
import android.support.v4.app.Fragment;
import android.support.v4.app.LoaderManager;
import android.support.v4.content.CursorLoader;
import android.support.v4.content.Loader;
import android.support.v7.widget.LinearLayoutManager;
import android.support.v7.widget.RecyclerView;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.view.WindowManager;
import android.widget.TextView;

import com.grappbox.grappbox.ProjectActivity;
import com.grappbox.grappbox.R;
import com.grappbox.grappbox.adapter.BugDetailAdapter;
import com.grappbox.grappbox.data.GrappboxContract;
import com.grappbox.grappbox.model.BugCommentModel;
import com.grappbox.grappbox.model.BugModel;
import com.grappbox.grappbox.model.BugTagModel;
import com.grappbox.grappbox.model.UserModel;

import java.util.ArrayList;
import java.util.Collection;
import java.util.List;

/**
 * A simple {@link Fragment} subclass.
 */
public class BugDetailsFragment extends Fragment implements LoaderManager.LoaderCallbacks<Cursor> {
    private RecyclerView mRecycler;
    private BugDetailAdapter mAdapter;
    private TextView mBugDescription;
    private BugModel mModel;

    private int LOADER_COMMENT = 0;
    private int LOADER_BUG = 1;
    public BugDetailsFragment() {
        // Required empty public constructor
    }


    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        super.onCreateView(inflater, container, savedInstanceState);
        mModel = getActivity().getIntent().getParcelableExtra(BugDetailsActivity.EXTRA_BUG_MODEL);
        View v = inflater.inflate(R.layout.bugtracker_details, container, false);
        mRecycler = (RecyclerView) v.findViewById(R.id.scrollable_content);
        mAdapter = new BugDetailAdapter(getActivity());
        mAdapter.setReplyClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                mRecycler.scrollToPosition(mRecycler.getChildCount());
            }
        });
        mRecycler.setAdapter(mAdapter);
        mRecycler.setLayoutManager(new LinearLayoutManager(getActivity()));
        mBugDescription = (TextView) v.findViewById(R.id.description);
        mBugDescription.setText(mModel.desc);
        return v;
    }


    @Override
    public void onActivityCreated(@Nullable Bundle savedInstanceState) {
        super.onActivityCreated(savedInstanceState);
        getActivity().getSupportLoaderManager().initLoader(0, null, this);
        getActivity().getSupportLoaderManager().initLoader(LOADER_BUG, null, this);
    }

    @Override
    public Loader<Cursor> onCreateLoader(int id, Bundle args) {
        if (id == LOADER_COMMENT){
            final String[] projectionComments = {
                    GrappboxContract.BugEntry.TABLE_NAME + "." + GrappboxContract.BugEntry._ID,
                    GrappboxContract.BugEntry.TABLE_NAME + "." + GrappboxContract.BugEntry.COLUMN_GRAPPBOX_ID,
                    GrappboxContract.UserEntry.TABLE_NAME + "." + GrappboxContract.UserEntry.COLUMN_FIRSTNAME,
                    GrappboxContract.UserEntry.TABLE_NAME + "." + GrappboxContract.UserEntry.COLUMN_LASTNAME,
                    GrappboxContract.UserEntry.TABLE_NAME + "." + GrappboxContract.UserEntry.COLUMN_URI_AVATAR,
                    GrappboxContract.UserEntry.TABLE_NAME + "." + GrappboxContract.UserEntry.COLUMN_CONTACT_EMAIL,
                    GrappboxContract.BugEntry.TABLE_NAME + "." + GrappboxContract.BugEntry.COLUMN_DESCRIPTION,
                    GrappboxContract.BugEntry.TABLE_NAME + "." + GrappboxContract.BugEntry.COLUMN_DATE_LAST_EDITED_UTC

            };
            String selectionComments = GrappboxContract.BugEntry.COLUMN_LOCAL_PARENT_ID + "=?";
            String order = "datetime("+GrappboxContract.BugEntry.TABLE_NAME + "." + GrappboxContract.BugEntry.COLUMN_DATE_LAST_EDITED_UTC+") ASC";
            Log.e("TEST", String.valueOf(mModel._id));
            return new CursorLoader(getActivity(), GrappboxContract.BugEntry.buildBugWithCreator(), projectionComments, selectionComments, new String[]{String.valueOf(mModel._id)}, order);
        }
        return new CursorLoader(getActivity(), GrappboxContract.BugEntry.CONTENT_URI, null, GrappboxContract.BugEntry._ID+"=?", new String[]{String.valueOf(mModel._id)}, null);
    }

    @Override
    public void onLoadFinished(Loader<Cursor> loader, Cursor data) {
        if (loader.getId() == LOADER_COMMENT){
            BugModel model = getActivity().getIntent().getParcelableExtra(BugDetailsActivity.EXTRA_BUG_MODEL);
            List<BugCommentModel> comments = new ArrayList<>();
            if (data != null){
                if (data.moveToFirst()){
                    do {
                        comments.add(new BugCommentModel(data));
                    } while (data.moveToNext());
                }
            }
            model.setCommentsData(comments);
            mAdapter.setComments(comments);
        } else{
            data.moveToFirst();
            mModel.setCoreData(getActivity(), data);
            getActivity().setTitle(mModel.title);
            mBugDescription.setText(mModel.desc);

            (new AdditionalDataLoader()).execute(mModel);
        }
    }

    @Override
    public void onLoaderReset(Loader<Cursor> loader) {

    }

    private class AdditionalDataLoader extends AsyncTask<BugModel, Void, BugModel> {

        @Override
        protected final BugModel doInBackground(BugModel... params) {
            if (params == null || params.length < 1)
                throw new IllegalArgumentException();
            final String[] projectionAssignee = {
                    GrappboxContract.UserEntry.TABLE_NAME + "." + GrappboxContract.UserEntry._ID,
                    GrappboxContract.UserEntry.TABLE_NAME + "." + GrappboxContract.UserEntry.COLUMN_FIRSTNAME,
                    GrappboxContract.UserEntry.TABLE_NAME + "." + GrappboxContract.UserEntry.COLUMN_LASTNAME,
                    GrappboxContract.UserEntry.TABLE_NAME + "." + GrappboxContract.UserEntry.COLUMN_DATE_BIRTHDAY_UTC,
                    GrappboxContract.UserEntry.TABLE_NAME + "." + GrappboxContract.UserEntry.COLUMN_URI_AVATAR,
                    GrappboxContract.UserEntry.TABLE_NAME + "." + GrappboxContract.UserEntry.COLUMN_CONTACT_EMAIL
            };
            String selectionAssignee = GrappboxContract.BugAssignationEntry.COLUMN_LOCAL_BUG_ID+"=?";
            final String[] projectionTags = {
                    GrappboxContract.BugtrackerTagEntry.TABLE_NAME + "." + GrappboxContract.BugtrackerTagEntry._ID,
                    GrappboxContract.BugtrackerTagEntry.TABLE_NAME + "." + GrappboxContract.BugtrackerTagEntry.COLUMN_NAME
            };
            String selectionTags = GrappboxContract.BugTagEntry.COLUMN_LOCAL_BUG_ID + "=?";
            BugModel model = params[0];
            long projectId = model.projectID;
            String[] args = {
                    String.valueOf(model._id)
            };
            Cursor resultAssignee = getActivity().getContentResolver().query(GrappboxContract.BugAssignationEntry.CONTENT_URI, projectionAssignee, selectionAssignee, args, null);
            List<UserModel> assignees = new ArrayList<>();
            if (resultAssignee != null){
                if (resultAssignee.moveToFirst()){
                    do{
                        assignees.add(new UserModel(resultAssignee));
                    } while (resultAssignee.moveToNext());
                }
                resultAssignee.close();
            }
            model.setAssigneesData(assignees);
            Cursor resultTags = getActivity().getContentResolver().query(GrappboxContract.BugEntry.buildBugWithTag(), projectionTags, selectionTags, args, null);
            List<BugTagModel> tags = new ArrayList<>();
            if (resultTags != null){
                if (resultTags.moveToFirst()){
                    do{
                        tags.add(new BugTagModel(resultTags));
                    } while (resultTags.moveToNext());
                }
                resultTags.close();
            }
            model.setTagsData(tags);
            model.setProjectID(getActivity().getIntent().getLongExtra(ProjectActivity.EXTRA_PROJECT_ID, -1));
            model.projectID = projectId;
            return model;
        }

        @Override
        protected void onPostExecute(BugModel data) {
            super.onPostExecute(data);
            mModel = data;
            mAdapter.setBugModel(mModel);
            Intent newInt = getActivity().getIntent();
            newInt.removeExtra(BugDetailsActivity.EXTRA_BUG_MODEL);
            newInt.putExtra(BugDetailsActivity.EXTRA_BUG_MODEL, mModel);
            getActivity().setIntent(newInt);
        }
    }
}

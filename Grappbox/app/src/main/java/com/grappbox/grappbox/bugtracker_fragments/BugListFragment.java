package com.grappbox.grappbox.bugtracker_fragments;


import android.accounts.AccountManager;
import android.content.Intent;
import android.database.Cursor;
import android.os.AsyncTask;
import android.os.Bundle;
import android.os.Handler;
import android.support.annotation.Nullable;
import android.support.v4.app.Fragment;
import android.support.v4.app.LoaderManager;
import android.support.v4.content.ContextCompat;
import android.support.v4.content.CursorLoader;
import android.support.v4.content.Loader;
import android.support.v4.widget.SwipeRefreshLayout;
import android.support.v7.widget.LinearLayoutManager;
import android.support.v7.widget.RecyclerView;
import android.support.v7.widget.RecyclerView.AdapterDataObserver;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ProgressBar;

import com.grappbox.grappbox.ProjectActivity;
import com.grappbox.grappbox.R;
import com.grappbox.grappbox.adapter.BugListAdapter;
import com.grappbox.grappbox.data.GrappboxContract;
import com.grappbox.grappbox.data.GrappboxContract.BugEntry;
import com.grappbox.grappbox.item_decoration.HorizontalDivider;
import com.grappbox.grappbox.model.BugCommentModel;
import com.grappbox.grappbox.model.BugModel;
import com.grappbox.grappbox.model.BugTagModel;
import com.grappbox.grappbox.model.UserModel;
import com.grappbox.grappbox.receiver.RefreshReceiver;
import com.grappbox.grappbox.singleton.Session;
import com.grappbox.grappbox.sync.BugtrackerJIT;
import com.grappbox.grappbox.sync.GrappboxJustInTimeService;

import java.text.ParseException;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Collection;
import java.util.Collections;
import java.util.Comparator;
import java.util.List;

/**
 * A simple {@link Fragment} subclass.
 */
public class BugListFragment extends Fragment implements LoaderManager.LoaderCallbacks<Cursor>, SwipeRefreshLayout.OnRefreshListener {

    public static final String ARG_LIST_TYPE = "com.grappbox.grappbox.bugtracker_fragment.ARG_LIST_TYPE";
    public static final int TYPE_OPEN = 0;
    public static final int TYPE_CLOSE = 1;
    public static final int TYPE_YOURS = 2;
    private static final String LOG_TAG = BugListFragment.class.getSimpleName();

    private BugListAdapter mAdapter;
    private RecyclerView mBuglist;
    private SwipeRefreshLayout mRefresher;
    private RefreshReceiver mRefreshReceiver = null;
    private ProgressBar mLoader;

    public BugListFragment() {
        // Required empty public constructor
    }


    @Override
    public void onActivityCreated(@Nullable Bundle savedInstanceState) {
        super.onActivityCreated(savedInstanceState);
        getLoaderManager().initLoader(getArguments().getInt(ARG_LIST_TYPE), null, this);
    }

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        // Inflate the layout for this fragment
        mAdapter = new BugListAdapter(getActivity());
        View v = inflater.inflate(R.layout.fragment_bug_list, container, false);
        mBuglist = (RecyclerView) v.findViewById(R.id.buglist);
        mBuglist.setAdapter(mAdapter);
        mBuglist.addItemDecoration(new HorizontalDivider(ContextCompat.getColor(getActivity(), R.color.GrappGrayMedium)));
        mBuglist.setLayoutManager(new LinearLayoutManager(getActivity()));
        mRefresher = (SwipeRefreshLayout) v.findViewById(R.id.refresh);
        mLoader = (ProgressBar) v.findViewById(R.id.loader);
        mAdapter.registerAdapterDataObserver(new AdapterObserver());

        mRefreshReceiver = new RefreshReceiver(new Handler(), mRefresher, getActivity());
        mRefresher.setOnRefreshListener(this);
        Log.d(LOG_TAG, "Saved Instance State : " + savedInstanceState);
        if (savedInstanceState != null)
            mRefresher.setVisibility(View.VISIBLE);
        return v;
    }

    @Override
    public Loader<Cursor> onCreateLoader(int id, Bundle args) {
        String selection;
        String sortOrder = null;
        String[] selectionArgs;
        long lpid = getActivity().getIntent().getLongExtra(ProjectActivity.EXTRA_PROJECT_ID, -1);

        switch (id) {
            case TYPE_OPEN:
                sortOrder = "date(" + BugEntry.COLUMN_GRAPPBOX_ID + ") DESC";
                selection = BugEntry.TABLE_NAME + "." + BugEntry.COLUMN_LOCAL_PROJECT_ID + "=? AND " + BugEntry.COLUMN_LOCAL_PARENT_ID + " IS NULL AND " + BugEntry.COLUMN_DATE_DELETED_UTC + " IS NULL";
                selectionArgs = new String[]{
                        String.valueOf(lpid)
                };
                break;
            case TYPE_CLOSE:
                sortOrder = "datetime(" + BugEntry.COLUMN_DATE_DELETED_UTC + ") DESC";
                selection = BugEntry.TABLE_NAME + "." + BugEntry.COLUMN_LOCAL_PROJECT_ID + "=? AND " + BugEntry.COLUMN_LOCAL_PARENT_ID + " IS NULL AND " + BugEntry.COLUMN_DATE_DELETED_UTC + " IS NOT NULL";
                selectionArgs = new String[]{
                        String.valueOf(lpid)
                };
                break;

            case TYPE_YOURS:
                sortOrder = "date(" + BugEntry.COLUMN_DATE_LAST_EDITED_UTC + ") DESC";
                String[] projection = {
                        BugEntry.TABLE_NAME + "." + BugEntry._ID,
                        BugEntry.TABLE_NAME + "." + BugEntry.COLUMN_GRAPPBOX_ID,
                        BugEntry.TABLE_NAME + "." + BugEntry.COLUMN_TITLE,
                        BugEntry.TABLE_NAME + "." + BugEntry.COLUMN_DESCRIPTION,
                        BugEntry.TABLE_NAME + "." + BugEntry.COLUMN_DATE_LAST_EDITED_UTC,
                        BugEntry.TABLE_NAME + "." + BugEntry.COLUMN_DATE_DELETED_UTC
                };
                long uid = Session.getInstance(getActivity()).getSelectedProject();

                selection = BugEntry.TABLE_NAME + "." + BugEntry.COLUMN_LOCAL_PROJECT_ID + "=? AND " + BugEntry.COLUMN_LOCAL_PARENT_ID + " IS NULL AND " + BugEntry.COLUMN_DATE_DELETED_UTC + " IS NULL AND " +
                        GrappboxContract.BugAssignationEntry.TABLE_NAME + "." + GrappboxContract.BugAssignationEntry.COLUMN_LOCAL_USER_ID + "=?";
                selectionArgs = new String[]{
                        String.valueOf(lpid),
                        String.valueOf(uid)
                };
                return new CursorLoader(getActivity(), BugEntry.buildBugWithAssignation(), projection, selection, selectionArgs, sortOrder);
            default:
                throw new IllegalArgumentException("Type doesn't exist");
        }
        return new CursorLoader(getActivity(), BugEntry.CONTENT_URI, null, selection, selectionArgs, sortOrder);
    }

    @Override
    public void onLoadFinished(Loader<Cursor> loader, Cursor data) {
        if (!data.moveToFirst()){
            mLoader.setVisibility(View.GONE);
            mRefresher.setVisibility(View.VISIBLE);
            return;
        }

        List<BugModel> models = new ArrayList<>();
        do {
            models.add(new BugModel(getActivity(), data));
        } while (data.moveToNext());
        AdditionalDataLoader task = new AdditionalDataLoader();

        //noinspection unchecked
        task.execute(models);
    }

    @Override
    public void onLoaderReset(Loader<Cursor> loader) {
    }

    @Override
    public void onRefresh() {
        AccountManager am = AccountManager.get(getActivity());

        long uid = Long.parseLong(am.getUserData(Session.getInstance(getActivity()).getCurrentAccount(), GrappboxJustInTimeService.EXTRA_USER_ID));
        Intent bugSync = new Intent(getActivity(), BugtrackerJIT.class);
        bugSync.setAction(BugtrackerJIT.ACTION_SYNC_BUGS);

        bugSync.putExtra(GrappboxJustInTimeService.EXTRA_RESPONSE_RECEIVER, mRefreshReceiver);
        bugSync.putExtra(GrappboxJustInTimeService.EXTRA_USER_ID, uid);
        bugSync.putExtra(GrappboxJustInTimeService.EXTRA_PROJECT_ID, getActivity().getIntent().getLongExtra(ProjectActivity.EXTRA_PROJECT_ID, -1));
        if (getArguments().getInt(ARG_LIST_TYPE) == TYPE_CLOSE)
            bugSync.addCategory(BugtrackerJIT.CATEGORY_CLOSED);
        Log.e("TEST", "start service bugtrackerJIT");
        getActivity().startService(bugSync);
    }

    class AdapterObserver extends AdapterDataObserver{
        @Override
        public void onChanged() {
            super.onChanged();
            if (!mAdapter.isEmpty()){

            }
        }
    }

    private class AdditionalDataLoader extends AsyncTask<Collection<BugModel>, Void, Collection<BugModel>>{

        @SafeVarargs
        @Override
        protected final Collection<BugModel> doInBackground(Collection<BugModel>... params) {
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
            final String[] projectionComments = {
                    BugEntry.TABLE_NAME + "." + BugEntry._ID,
                    BugEntry.TABLE_NAME + "." + BugEntry.COLUMN_GRAPPBOX_ID,
                    GrappboxContract.UserEntry.TABLE_NAME + "." + GrappboxContract.UserEntry.COLUMN_FIRSTNAME,
                    GrappboxContract.UserEntry.TABLE_NAME + "." + GrappboxContract.UserEntry.COLUMN_LASTNAME,
                    GrappboxContract.UserEntry.TABLE_NAME + "." + GrappboxContract.UserEntry.COLUMN_URI_AVATAR,
                    GrappboxContract.UserEntry.TABLE_NAME + "." + GrappboxContract.UserEntry.COLUMN_CONTACT_EMAIL,
                    BugEntry.TABLE_NAME + "." + BugEntry.COLUMN_DESCRIPTION,
                    BugEntry.TABLE_NAME + "." + BugEntry.COLUMN_DATE_LAST_EDITED_UTC

            };
            String selectionComments = BugEntry.COLUMN_LOCAL_PARENT_ID + "=?";
            final String[] projectionTags = {
                    GrappboxContract.BugtrackerTagEntry.TABLE_NAME + "." + GrappboxContract.BugtrackerTagEntry._ID,
                    GrappboxContract.BugtrackerTagEntry.TABLE_NAME + "." + GrappboxContract.BugtrackerTagEntry.COLUMN_NAME
            };
            String selectionTags = GrappboxContract.BugTagEntry.COLUMN_LOCAL_BUG_ID + "=?";
            for (BugModel model : params[0]){
                String[] args = {
                        String.valueOf(model._id)
                };
                long countComments = 0;
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
                Cursor resultComments = getActivity().getContentResolver().query(GrappboxContract.BugEntry.buildBugWithCreator(), projectionComments, selectionComments, args, null);
                List<BugCommentModel> comments = new ArrayList<>();
                if (resultComments != null){
                    if (resultComments.moveToFirst()){
                        do {
                            comments.add(new BugCommentModel(resultComments));
                        } while (resultComments.moveToNext());
                    }
                    resultComments.close();
                }
                model.setCommentsData(comments);
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
            }
            return params[0];
        }

        @Override
        protected void onPostExecute(Collection<BugModel> data) {
            super.onPostExecute(data);
            mAdapter.clear();
            mAdapter.add(data);
            mLoader.setVisibility(View.GONE);
            mRefresher.setVisibility(View.VISIBLE);
        }
    }
}

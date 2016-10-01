package com.grappbox.grappbox.bugtracker_fragments;


import android.accounts.AccountManager;
import android.content.Intent;
import android.database.Cursor;
import android.graphics.Color;
import android.os.AsyncTask;
import android.os.Bundle;
import android.os.Handler;
import android.os.SystemClock;
import android.os.Trace;
import android.support.annotation.Nullable;
import android.support.v4.app.Fragment;
import android.support.v4.app.LoaderManager;
import android.support.v4.content.ContextCompat;
import android.support.v4.content.CursorLoader;
import android.support.v4.content.Loader;
import android.support.v4.util.Pair;
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
import com.grappbox.grappbox.data.GrappboxContract.UserEntry;
import com.grappbox.grappbox.model.BugModel;
import com.grappbox.grappbox.receiver.RefreshReceiver;
import com.grappbox.grappbox.singleton.Session;
import com.grappbox.grappbox.sync.GrappboxJustInTimeService;

import java.util.Collection;
import java.util.HashSet;

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
        if (savedInstanceState == null)
            getLoaderManager().initLoader(getArguments().getInt(ARG_LIST_TYPE), null, this);
        else
            getLoaderManager().restartLoader(getArguments().getInt(ARG_LIST_TYPE), null, this);
    }

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        // Inflate the layout for this fragment
        mAdapter = new BugListAdapter(getActivity());
        View v = inflater.inflate(R.layout.fragment_bug_list, container, false);
        mBuglist = (RecyclerView) v.findViewById(R.id.buglist);
        mBuglist.setAdapter(mAdapter);
        mBuglist.setLayoutManager(new LinearLayoutManager(getActivity()));
        mRefresher = (SwipeRefreshLayout) v.findViewById(R.id.refresh);
        mLoader = (ProgressBar) v.findViewById(R.id.loader);
        mAdapter.registerAdapterDataObserver(new AdapterObserver());

        mRefreshReceiver = new RefreshReceiver(new Handler(), mRefresher, getActivity());
        mRefresher.setOnRefreshListener(this);
        Trace.endSection();
        return v;
    }

    @Override
    public Loader<Cursor> onCreateLoader(int id, Bundle args) {
        String selection, sortOrder = "datetime(" + BugEntry.COLUMN_DATE_LAST_EDITED_UTC + ") DESC";
        String[] selectionArgs;
        long lpid = getActivity().getIntent().getLongExtra(ProjectActivity.EXTRA_PROJECT_ID, -1);

        switch (id) {
            case TYPE_OPEN:
                selection = BugEntry.TABLE_NAME + "." + BugEntry.COLUMN_LOCAL_PROJECT_ID + "=? AND " + BugEntry.COLUMN_LOCAL_PARENT_ID + " IS NULL AND " + BugEntry.COLUMN_DATE_DELETED_UTC + " IS NULL";
                selectionArgs = new String[]{
                        String.valueOf(lpid)
                };
                break;
            case TYPE_CLOSE:
                selection = BugEntry.TABLE_NAME + "." + BugEntry.COLUMN_LOCAL_PROJECT_ID + "=? AND " + BugEntry.COLUMN_LOCAL_PARENT_ID + " IS NULL AND " + BugEntry.COLUMN_DATE_DELETED_UTC + " IS NOT NULL";
                selectionArgs = new String[]{
                        String.valueOf(lpid)
                };
                break;

            case TYPE_YOURS:
                String[] projection = {
                    BugEntry.TABLE_NAME + "." + BugEntry._ID,
                    BugEntry.COLUMN_GRAPPBOX_ID,
                    BugEntry.COLUMN_TITLE,
                    BugEntry.COLUMN_DESCRIPTION,
                    BugEntry.COLUMN_DATE_LAST_EDITED_UTC,
                    BugEntry.COLUMN_DATE_DELETED_UTC
                };
                long uid = Long.parseLong(AccountManager.get(getActivity()).getUserData(Session.getInstance(getActivity()).getCurrentAccount(), GrappboxJustInTimeService.EXTRA_USER_ID));

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
        if (!data.moveToFirst())
            return;
        Collection<BugModel> models = new HashSet<>();
        do {
            models.add(new BugModel(getActivity(), data));
        } while (data.moveToNext());
        AdditionalDataLoader task = new AdditionalDataLoader();
        task.execute(models);
    }

    @Override
    public void onLoaderReset(Loader<Cursor> loader) {
    }

    @Override
    public void onRefresh() {
        AccountManager am = AccountManager.get(getActivity());
        long uid = Long.parseLong(am.getUserData(Session.getInstance(getActivity()).getCurrentAccount(), GrappboxJustInTimeService.EXTRA_USER_ID));
        Intent bugSync = new Intent(getActivity(), GrappboxJustInTimeService.class);
        bugSync.setAction(GrappboxJustInTimeService.ACTION_SYNC_BUGS);

        bugSync.putExtra(GrappboxJustInTimeService.EXTRA_RESPONSE_RECEIVER, mRefreshReceiver);
        bugSync.putExtra(GrappboxJustInTimeService.EXTRA_USER_ID, uid);
        bugSync.putExtra(GrappboxJustInTimeService.EXTRA_PROJECT_ID, getActivity().getIntent().getLongExtra(ProjectActivity.EXTRA_PROJECT_ID, -1));
        if (getArguments().getInt(ARG_LIST_TYPE) == TYPE_CLOSE)
            bugSync.addCategory(GrappboxJustInTimeService.CATEGORY_CLOSED);
        getActivity().startService(bugSync);
    }

    class AdapterObserver extends AdapterDataObserver{
        @Override
        public void onChanged() {
            super.onChanged();
            if (!mAdapter.isEmpty()){
                mLoader.setVisibility(View.GONE);
                mRefresher.setVisibility(View.VISIBLE);
            }
        }
    }

    private class AdditionalDataLoader extends AsyncTask<Collection<BugModel>, Void, Collection<BugModel>>{

        @SafeVarargs
        @Override
        protected final Collection<BugModel> doInBackground(Collection<BugModel>... params) {
            if (params == null || params.length < 1)
                throw new IllegalArgumentException();
            String[] projectionAssignee = {
                GrappboxContract.BugAssignationEntry._ID
            };
            String selectionAssignee = GrappboxContract.BugAssignationEntry.COLUMN_LOCAL_BUG_ID+"=?";
            String[] projectionComments = {
                    BugEntry.COLUMN_GRAPPBOX_ID
            };
            String selectionComments = BugEntry.COLUMN_LOCAL_PARENT_ID + "=?";
            String[] projectionTags = {
                    GrappboxContract.TagEntry.TABLE_NAME + "." + GrappboxContract.TagEntry.COLUMN_NAME
            };
            String selectionTags = GrappboxContract.BugTagEntry.COLUMN_LOCAL_BUG_ID + "=?";
            for (BugModel model : params[0]){
                String[] args = {
                        String.valueOf(model._id)
                };
                long countAssignee = 0, countComments = 0;
                Cursor resultAssignee = getActivity().getContentResolver().query(GrappboxContract.BugAssignationEntry.CONTENT_URI, projectionAssignee, selectionAssignee, args, null);
                if (resultAssignee != null){
                    countAssignee = resultAssignee.getCount();
                    resultAssignee.close();
                }
                Cursor resultComments = getActivity().getContentResolver().query(GrappboxContract.BugEntry.CONTENT_URI, projectionComments, selectionComments, args, null);
                if (resultComments != null && resultComments.moveToFirst()){
                    countComments = resultComments.getCount();
                    resultComments.close();
                }

                Cursor resultTags = getActivity().getContentResolver().query(GrappboxContract.BugEntry.buildBugWithTag(), projectionTags, selectionTags, args, null);
                Collection<Pair<String, String>> tags = new HashSet<>();
                if (resultTags != null && resultTags.moveToFirst()){
                    do{
                        tags.add(new Pair<>(resultTags.getString(0), Integer.toHexString(ContextCompat.getColor(getActivity(), R.color.GrappPurple))));
                    } while (resultTags.moveToNext());
                    resultTags.close();
                }
                model.setAdditionalData(countAssignee, countComments, tags);
            }
            return params[0];
        }

        @Override
        protected void onPostExecute(Collection<BugModel> data) {
            super.onPostExecute(data);
            mAdapter.clear();
            mAdapter.add(data);
        }
    }
}

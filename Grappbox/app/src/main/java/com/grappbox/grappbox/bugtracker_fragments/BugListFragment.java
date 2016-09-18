package com.grappbox.grappbox.bugtracker_fragments;


import android.accounts.AccountManager;
import android.content.Intent;
import android.database.Cursor;
import android.database.DatabaseUtils;
import android.os.Bundle;
import android.os.Handler;
import android.support.annotation.Nullable;
import android.support.v4.app.Fragment;
import android.support.v4.app.LoaderManager;
import android.support.v4.content.CursorLoader;
import android.support.v4.content.Loader;
import android.support.v4.widget.SwipeRefreshLayout;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ListView;

import com.grappbox.grappbox.ProjectActivity;
import com.grappbox.grappbox.R;
import com.grappbox.grappbox.Utils;
import com.grappbox.grappbox.adapter.BugListAdapter;
import com.grappbox.grappbox.data.GrappboxContract;
import com.grappbox.grappbox.data.GrappboxContract.BugAssignationEntry;
import com.grappbox.grappbox.data.GrappboxContract.BugEntry;
import com.grappbox.grappbox.data.GrappboxContract.TagEntry;
import com.grappbox.grappbox.data.GrappboxContract.UserEntry;
import com.grappbox.grappbox.receiver.RefreshReceiver;
import com.grappbox.grappbox.singleton.Session;
import com.grappbox.grappbox.sync.GrappboxJustInTimeService;

/**
 * A simple {@link Fragment} subclass.
 */
public class BugListFragment extends Fragment implements LoaderManager.LoaderCallbacks<Cursor> {
    public static final int LOADER_LIST = 0;

    public static final String ARG_LIST_TYPE = "com.grappbox.grappbox.bugtracker_fragment.ARG_LIST_TYPE";
    public static final int TYPE_OPEN = 0;
    public static final int TYPE_CLOSE = 1;
    public static final int TYPE_YOURS = 2;
    private static final String LOG_TAG = BugListFragment.class.getSimpleName();

    private BugListAdapter mAdapter;
    private ListView mBuglist;
    private SwipeRefreshLayout mRefresher;
    private RefreshReceiver mRefreshReceiver;

    public BugListFragment() {
        // Required empty public constructor
    }


    @Override
    public void onActivityCreated(@Nullable Bundle savedInstanceState) {
        super.onActivityCreated(savedInstanceState);
        getLoaderManager().initLoader(LOADER_LIST, getArguments(), this);
    }

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        // Inflate the layout for this fragment
        View v = inflater.inflate(R.layout.fragment_bug_list, container, false);
        mBuglist = (ListView) v.findViewById(R.id.buglist);
        mRefresher = (SwipeRefreshLayout) v.findViewById(R.id.refresh);
        mAdapter = new BugListAdapter(getActivity(), null, 0);
        mBuglist.setAdapter(mAdapter);
        if (savedInstanceState != null) {
            getLoaderManager().initLoader(LOADER_LIST, getArguments(), this);
        }
        mRefreshReceiver = new RefreshReceiver(new Handler(), mRefresher, getActivity());
        mRefresher.setOnRefreshListener(new SwipeRefreshLayout.OnRefreshListener() {
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
        });
        return v;
    }

    @Override
    public Loader<Cursor> onCreateLoader(int id, Bundle args) {
        Log.d(LOG_TAG, "onCreateLoader");
        String selection, sortOrder = "datetime("+BugEntry.COLUMN_DATE_LAST_EDITED_UTC + ") DESC";
        String[] selectionArgs;

        switch (args.getInt(ARG_LIST_TYPE)){
            case TYPE_OPEN:
                selection = BugEntry.TABLE_NAME + "." + BugEntry.COLUMN_LOCAL_PROJECT_ID + "=? AND " + BugEntry.COLUMN_LOCAL_PARENT_ID + " IS NULL AND " + BugEntry.COLUMN_DATE_DELETED_UTC + " IS NULL";
                selectionArgs = new String[]{
                        String.valueOf(getActivity().getIntent().getLongExtra(ProjectActivity.EXTRA_PROJECT_ID, -1))
                };
                break;
            case TYPE_CLOSE:
                selection = BugEntry.TABLE_NAME + "." + BugEntry.COLUMN_LOCAL_PROJECT_ID + "=? AND " + BugEntry.COLUMN_LOCAL_PARENT_ID + " IS NULL AND " + BugEntry.COLUMN_DATE_DELETED_UTC + " IS NOT NULL";
                selectionArgs = new String[]{
                        String.valueOf(getActivity().getIntent().getLongExtra(ProjectActivity.EXTRA_PROJECT_ID, -1))
                };
                break;
            case TYPE_YOURS:
                long uid = Long.parseLong(AccountManager.get(getActivity()).getUserData(Session.getInstance(getActivity()).getCurrentAccount(), GrappboxJustInTimeService.EXTRA_USER_ID));
                Log.d(LOG_TAG, "UID = " + uid);
                selection = BugEntry.TABLE_NAME + "." + BugEntry.COLUMN_LOCAL_PROJECT_ID + "=? AND " + BugEntry.COLUMN_LOCAL_PARENT_ID + " IS NULL AND " + BugEntry.COLUMN_DATE_DELETED_UTC + " IS NULL AND " +
                        UserEntry.TABLE_NAME + "." + UserEntry._ID + "=?";
                selectionArgs = new String[]{
                        String.valueOf(getActivity().getIntent().getLongExtra(ProjectActivity.EXTRA_PROJECT_ID, -1)),
                        String.valueOf(uid)
                };
                return new CursorLoader(getActivity(), BugEntry.buildBugWithAssignation(), BugListAdapter.projection, selection, selectionArgs, sortOrder);
            default:
                throw new IllegalArgumentException("Type doesn't exist");
        }
        return new CursorLoader(getActivity(), BugEntry.CONTENT_URI, BugListAdapter.projection, selection, selectionArgs, sortOrder);
    }

    @Override
    public void onLoadFinished(Loader<Cursor> loader, Cursor data) {
        if (getArguments().getInt(ARG_LIST_TYPE) == TYPE_YOURS)
            DatabaseUtils.dumpCursor(data);
        mAdapter.swapCursor(data);
    }

    @Override
    public void onLoaderReset(Loader<Cursor> loader) {
        mAdapter.swapCursor(null);
    }
}

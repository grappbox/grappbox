package com.grappbox.grappbox;

import android.accounts.Account;
import android.accounts.AccountManager;
import android.content.Intent;
import android.database.Cursor;
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
import android.widget.AdapterView;
import android.widget.CursorAdapter;
import android.widget.ListView;

import com.grappbox.grappbox.adapter.ProjectListAdapter;
import com.grappbox.grappbox.data.GrappboxContract;
import com.grappbox.grappbox.data.GrappboxContract.ProjectAccountEntry;
import com.grappbox.grappbox.data.GrappboxContract.ProjectEntry;
import com.grappbox.grappbox.receiver.RefreshReceiver;
import com.grappbox.grappbox.singleton.Session;
import com.grappbox.grappbox.sync.GrappboxJustInTimeService;

/**
 * A placeholder fragment containing a simple view.
 */
public class ChooseProjectActivityFragment extends Fragment implements LoaderManager.LoaderCallbacks<Cursor> {
    private CursorAdapter _adapter;
    private static final String LOG_TAG = ChooseProjectActivityFragment.class.getSimpleName();
    private static final int PROJECT_LOADER = 0;
    private SwipeRefreshLayout mPullRefresher;
    private RefreshReceiver mRefreshReceiver;

    public ChooseProjectActivityFragment() {
    }

    @Override
    public void onActivityCreated(@Nullable Bundle savedInstanceState) {
        getLoaderManager().initLoader(PROJECT_LOADER, null, this);
        super.onActivityCreated(savedInstanceState);
    }

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        Log.d(LOG_TAG, "onCreateView started");
        final AccountManager am = AccountManager.get(getActivity());

        View v = inflater.inflate(R.layout.fragment_choose_project, container, false);
        ListView projectList = (ListView) v.findViewById(R.id.list_projects);

        _adapter = new ProjectListAdapter(getActivity(), null, 0);
        mPullRefresher = (SwipeRefreshLayout) v.findViewById(R.id.refresh);
        mRefreshReceiver = new RefreshReceiver(new Handler(), mPullRefresher, getActivity());
        projectList.setAdapter(_adapter);
        projectList.setOnItemClickListener(new AdapterView.OnItemClickListener() {
            @Override
            public void onItemClick(AdapterView<?> adapterView, View view, int position, long id) {
                if (position == 0)
                    return;
                Cursor cursor = (Cursor) adapterView.getItemAtPosition(position);
                Session.getInstance(getActivity()).setSelectedProject(cursor.getLong(cursor.getColumnIndex(ProjectEntry._ID)));
                Intent launchDashboard = new Intent(getActivity(), ProjectActivity.class);

                launchDashboard.putExtra(ProjectActivity.EXTRA_PROJECT_ID, cursor.getLong(cursor.getColumnIndex(ProjectEntry._ID)));
                launchDashboard.putExtra(ProjectActivity.EXTRA_PROJECT_NAME, cursor.getString(cursor.getColumnIndex(ProjectEntry.COLUMN_NAME)));
                getActivity().startActivity(launchDashboard);
            }
        });
        mPullRefresher.setOnRefreshListener(new SwipeRefreshLayout.OnRefreshListener() {
            @Override
            public void onRefresh() {
                mPullRefresher.setRefreshing(true);

                Intent refreshProjects = new Intent(getActivity(), GrappboxJustInTimeService.class);
                refreshProjects.setAction(GrappboxJustInTimeService.ACTION_SYNC_PROJECT_LIST);
                refreshProjects.putExtra(GrappboxJustInTimeService.EXTRA_API_TOKEN, am.getUserData(Session.getInstance(getActivity()).getCurrentAccount(), GrappboxJustInTimeService.EXTRA_API_TOKEN));
                refreshProjects.putExtra(GrappboxJustInTimeService.EXTRA_ACCOUNT_NAME, Session.getInstance(getActivity()).getCurrentAccount().name);
                refreshProjects.putExtra(GrappboxJustInTimeService.EXTRA_RESPONSE_RECEIVER, mRefreshReceiver);

                getActivity().startService(refreshProjects);
            }
        });
        return v;
    }

    @Override
    public Loader<Cursor> onCreateLoader(int id, Bundle args) {
        Account currentAccount = Session.getInstance(getActivity()).getCurrentAccount();
        if (currentAccount == null)
            return null;
        Log.e("TEST", "loader creation");
        String selection = ProjectAccountEntry.TABLE_NAME + "." + ProjectAccountEntry.COLUMN_ACCOUNT_NAME + "=?";
        String[] selectionArgs = new String[]{
                Session.getInstance(getActivity()).getCurrentAccount().name
        };
        return new CursorLoader(getActivity(), ProjectAccountEntry.CONTENT_URI, null, selection, selectionArgs, null);
    }

    @Override
    public void onLoadFinished(Loader<Cursor> loader, Cursor data) {
        Log.e("TEST", String.valueOf(data.getCount()));
        _adapter.swapCursor(data);
    }

    @Override
    public void onLoaderReset(Loader<Cursor> loader) {
        _adapter.swapCursor(null);
    }

}



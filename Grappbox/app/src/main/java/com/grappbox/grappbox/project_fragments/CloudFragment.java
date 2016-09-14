package com.grappbox.grappbox.project_fragments;


import android.accounts.AccountManager;
import android.content.Intent;
import android.database.Cursor;
import android.os.Bundle;
import android.support.annotation.Nullable;
import android.support.design.widget.TabLayout;
import android.support.v4.app.Fragment;
import android.support.v4.app.LoaderManager;
import android.support.v4.content.CursorLoader;
import android.support.v4.content.Loader;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.AdapterView;
import android.widget.ListView;

import com.grappbox.grappbox.ProjectActivity;
import com.grappbox.grappbox.R;
import com.grappbox.grappbox.adapter.CloudListAdapter;
import com.grappbox.grappbox.data.GrappboxContract;
import com.grappbox.grappbox.data.GrappboxContract.CloudEntry;
import com.grappbox.grappbox.singleton.Session;
import com.grappbox.grappbox.sync.GrappboxJustInTimeService;

/**
 * A simple {@link Fragment} subclass.
 */
public class CloudFragment extends Fragment implements LoaderManager.LoaderCallbacks<Cursor>, TabLayout.OnTabSelectedListener, CloudListAdapter.CloudAdapterListener, AdapterView.OnItemClickListener {
    private static final int LOADER_LOAD_FILELIST = 0;
    private static final String BUNDLE_KEY_CLOUD_PATH = "com.grappbox.grappbox.project_fragments.CloudFragment.BUNDLE_KEY_CLOUD_PATH";
    private static final String LOG_TAG = CloudFragment.class.getSimpleName();

    private String mPath = "/";
    private TabLayout mBreadcrumb;
    private ListView mCloudEntries;
    private CloudListAdapter mAdapter;

    public CloudFragment() {
        // Required empty public constructor
    }

    @Override
    public void onActivityCreated(@Nullable Bundle savedInstanceState) {
        super.onActivityCreated(savedInstanceState);
        getLoaderManager().initLoader(LOADER_LOAD_FILELIST, null, this);
    }

    private void syncBreadcrumb()
    {
        if (mPath.equals("/"))
            return;
        String[] segments = mPath.split("/");
        mBreadcrumb.removeAllTabs();
        TabLayout.Tab root = mBreadcrumb.newTab();
        root.setText(R.string.cloud_root_symbol);
        mBreadcrumb.addTab(root);

        for (String segment : segments) {
            TabLayout.Tab newTab = mBreadcrumb.newTab();
            newTab.setText(segment);
            mBreadcrumb.addTab(newTab);
        }
    }

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        // Inflate the layout for this fragment
        AccountManager am = AccountManager.get(getActivity());
        String token = am.getUserData(Session.getInstance(getActivity()).getCurrentAccount(), GrappboxJustInTimeService.EXTRA_API_TOKEN);
        mPath = getArguments() == null ? "/" : getArguments().getString(BUNDLE_KEY_CLOUD_PATH);
        View v =  inflater.inflate(R.layout.fragment_cloud, container, false);

        mBreadcrumb = (TabLayout) v.findViewById(R.id.breadcrumb);
        syncBreadcrumb();
        mCloudEntries = (ListView) v.findViewById(R.id.cloud_entries);
        mAdapter = new CloudListAdapter(getActivity(), null, 0);
        mAdapter.setListener(this);
        mCloudEntries.setAdapter(mAdapter);
        mCloudEntries.setOnItemClickListener(this);

        Intent refreshList = new Intent(getActivity(), GrappboxJustInTimeService.class);
        refreshList.setAction(GrappboxJustInTimeService.ACTION_SYNC_CLOUD_PATH);
        refreshList.putExtra(GrappboxJustInTimeService.EXTRA_API_TOKEN, token);
        refreshList.putExtra(GrappboxJustInTimeService.EXTRA_CLOUD_PATH, mPath);
        refreshList.putExtra(GrappboxJustInTimeService.EXTRA_PROJECT_ID, getActivity().getIntent().getLongExtra(ProjectActivity.EXTRA_PROJECT_ID, -1));
        if (mPath.contains("/Safe")){
            refreshList.putExtra(GrappboxJustInTimeService.EXTRA_CLOUD_PASSWORD, ""); //TODO : Handle safe
        } else {
            refreshList.putExtra(GrappboxJustInTimeService.EXTRA_CLOUD_PASSWORD, "");
            getActivity().startService(refreshList);
        }


        return v;
    }

    @Override
    public Loader<Cursor> onCreateLoader(int id, Bundle args) {
        if (id == LOADER_LOAD_FILELIST){
            Log.d(LOG_TAG, "Loader created");
            String sortOrder = CloudEntry.COLUMN_TYPE + " DESC";
            String selection = CloudEntry.TABLE_NAME + "." + CloudEntry.COLUMN_LOCAL_PROJECT_ID + "=?";
            String[] selectArgs = new String[]{
                String.valueOf(getActivity().getIntent().getLongExtra(ProjectActivity.EXTRA_PROJECT_ID, -1))
            };
            return new CursorLoader(getActivity(), CloudEntry.buildWithProjectJoin(), CloudListAdapter.cloudProjection, selection, selectArgs, sortOrder);
        }
        return null;
    }

    @Override
    public void onLoadFinished(Loader loader, Cursor data) {
        if (loader.getId() == LOADER_LOAD_FILELIST){
            Log.d(LOG_TAG, "loading ended");
            mAdapter.swapCursor(data);
        }

    }

    @Override
    public void onLoaderReset(Loader loader) {
        if (loader.getId() == LOADER_LOAD_FILELIST)
            mAdapter.swapCursor(null);
    }

    @Override
    public void onTabSelected(TabLayout.Tab tab) {
        if (tab.getPosition() == mBreadcrumb.getTabCount() - 1)
            return;
        for (int i = mBreadcrumb.getTabCount() - 1; i > tab.getPosition(); --i){
            mBreadcrumb.removeTabAt(i);
            getActivity().getSupportFragmentManager().popBackStack();
            mPath = mPath.substring(0, mPath.lastIndexOf("/"));
        }
        getLoaderManager().restartLoader(LOADER_LOAD_FILELIST, null, this);
    }

    @Override
    public void onTabUnselected(TabLayout.Tab tab) {}

    @Override
    public void onTabReselected(TabLayout.Tab tab) {}

    @Override
    public void onMoreClicked(int position) {

    }

    @Override
    public void onItemClick(AdapterView<?> adapterView, View view, int i, long l) {
        Fragment newFragment = new CloudFragment();
        Cursor item = (Cursor) mAdapter.getItem(i);
        if (item.getColumnCount() == 2)
            return;

        if (item.getInt(item.getColumnIndex(CloudEntry.COLUMN_TYPE)) == 0) {
            onMoreClicked(item.getPosition());
            return;
        }

        Bundle arg = new Bundle();
        arg.putString(BUNDLE_KEY_CLOUD_PATH, mPath + item.getString(item.getColumnIndex(CloudEntry.COLUMN_FILENAME)) + "/");
        newFragment.setArguments(arg);
        getActivity().getSupportFragmentManager().beginTransaction().add(R.id.fragment_container, newFragment, ProjectActivity.FRAGMENT_TAG_CLOUD).addToBackStack(null).commit();
    }
}

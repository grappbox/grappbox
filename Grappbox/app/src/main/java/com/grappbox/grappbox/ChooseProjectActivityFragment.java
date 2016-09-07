package com.grappbox.grappbox;

import android.accounts.AccountManager;
import android.content.Intent;
import android.database.Cursor;
import android.support.v4.app.Fragment;
import android.os.Bundle;
import android.support.v4.app.LoaderManager;
import android.support.v4.content.CursorLoader;
import android.support.v4.content.Loader;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.AdapterView;
import android.widget.CursorAdapter;
import android.widget.ListView;

import com.grappbox.grappbox.adapter.ProjectListAdapter;
import com.grappbox.grappbox.data.GrappboxContract;

/**
 * A placeholder fragment containing a simple view.
 */
public class ChooseProjectActivityFragment extends Fragment implements LoaderManager.LoaderCallbacks<Cursor> {
    private CursorAdapter _adapter;

    public ChooseProjectActivityFragment() {
    }

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        View v = inflater.inflate(R.layout.fragment_choose_project, container, false);
        ListView projectList = (ListView) v.findViewById(R.id.list_projects);

        _adapter = new ProjectListAdapter(getActivity(), null, 0);
        projectList.setAdapter(_adapter);
        projectList.setOnItemClickListener(new AdapterView.OnItemClickListener() {
            @Override
            public void onItemClick(AdapterView<?> adapterView, View view, int position, long id) {
                Cursor cursor = (Cursor) adapterView.getItemAtPosition(position);
                Intent launchDashboard = new Intent(getActivity(), DashboardActivity.class);

                launchDashboard.putExtra(DashboardActivity.EXTRA_PROJECT_ID, cursor.getLong(cursor.getColumnIndex(GrappboxContract.ProjectEntry._ID)));
                getActivity().startActivity(launchDashboard);
            }
        });
        return v;
    }

    @Override
    public Loader<Cursor> onCreateLoader(int id, Bundle args) {
        return new CursorLoader(getActivity(), GrappboxContract.ProjectEntry.CONTENT_URI, null, null, null, null);
    }

    @Override
    public void onLoadFinished(Loader<Cursor> loader, Cursor data) {
        _adapter.swapCursor(data);
    }

    @Override
    public void onLoaderReset(Loader<Cursor> loader) {
        _adapter.swapCursor(null);
    }
}

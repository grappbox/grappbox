package com.grappbox.grappbox.statistic_fragment;

import android.database.Cursor;
import android.os.Bundle;
import android.support.annotation.Nullable;
import android.support.v4.app.Fragment;
import android.support.v4.app.LoaderManager;
import android.support.v4.content.CursorLoader;
import android.support.v4.content.Loader;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.TextView;

import com.grappbox.grappbox.ProjectActivity;
import com.grappbox.grappbox.R;
import com.grappbox.grappbox.data.GrappboxContract;

/**
 * Created by tan_f on 16/01/2017.
 */

public class StatisticTimelineFragment extends Fragment implements LoaderManager.LoaderCallbacks<Cursor> {

    private TextView mNbCustomerMessages;
    private TextView mNbTeamMessages;

    @Nullable
    @Override
    public View onCreateView(LayoutInflater inflater, @Nullable ViewGroup container, @Nullable Bundle savedInstanceState) {
        View v = inflater.inflate(R.layout.statistic_timeline_fragment, container, false);
        mNbTeamMessages = (TextView)v.findViewById(R.id.timeline_label_team);
        mNbCustomerMessages = (TextView)v.findViewById(R.id.timeline_label_customer);
        return v;
    }

    @Override
    public Loader<Cursor> onCreateLoader(int id, Bundle args) {
        long lpid = getActivity().getIntent().getLongExtra(ProjectActivity.EXTRA_PROJECT_ID, -1);
        String selection;
        String[] selectionArgs;

        final String[] projectionAdvancement = {
                GrappboxContract.StatEntry.TABLE_NAME + "." + GrappboxContract.StatEntry._ID,
                GrappboxContract.StatEntry.TABLE_NAME + "." + GrappboxContract.StatEntry.COLUMN_TIMELINE_CUSTOMER_MESSAGE,
                GrappboxContract.StatEntry.TABLE_NAME + "." + GrappboxContract.StatEntry.COLUMN_TIMELINE_TEAM_MESSAGE,
        };
        selection = GrappboxContract.StatEntry.TABLE_NAME + "." + GrappboxContract.StatEntry.COLUMN_LOCAL_PROJECT_ID + "=?";
        selectionArgs = new String[] {String.valueOf(lpid)};

        return new CursorLoader(getActivity(), GrappboxContract.StatEntry.CONTENT_URI, projectionAdvancement, selection, selectionArgs, null);
    }

    @Override
    public void onLoadFinished(Loader<Cursor> loader, Cursor data) {
        if (data == null || !data.moveToFirst())
            return;
        mNbCustomerMessages.setText(data.getString(data.getColumnIndex(GrappboxContract.StatEntry.COLUMN_TIMELINE_CUSTOMER_MESSAGE)));
        mNbTeamMessages.setText(data.getString(data.getColumnIndex(GrappboxContract.StatEntry.COLUMN_TIMELINE_TEAM_MESSAGE)));
    }

    @Override
    public void onLoaderReset(Loader<Cursor> loader) {

    }
}

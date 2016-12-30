package com.grappbox.grappbox.statistic_fragment;

import android.database.Cursor;
import android.os.Bundle;
import android.support.annotation.Nullable;
import android.support.v4.app.Fragment;
import android.support.v4.app.LoaderManager;
import android.support.v4.content.Loader;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import com.github.mikephil.charting.charts.LineChart;
import com.grappbox.grappbox.ProjectActivity;
import com.grappbox.grappbox.R;
import com.grappbox.grappbox.data.GrappboxContract;

/**
 * Created by tan_f on 30/12/2016.
 */

public class StatisticAdvancementFragment extends Fragment implements LoaderManager.LoaderCallbacks<Cursor> {

    public static final String LOG_TAG = StatisticAdvancementFragment.class.getSimpleName();
    private LineChart mChart;

    @Nullable
    @Override
    public View onCreateView(LayoutInflater inflater, @Nullable ViewGroup container, @Nullable Bundle savedInstanceState) {
        View v = inflater.inflate(R.layout.statistic_advancement_stat, container, false);
        mChart = (LineChart) v.findViewById(R.id.advancement_line_chart);
        getLoaderManager().initLoader(0, null, this);
        return v;
    }

    @Override
    public Loader<Cursor> onCreateLoader(int id, Bundle args) {
        long lpid = getActivity().getIntent().getLongExtra(ProjectActivity.EXTRA_PROJECT_ID, -1);
        String sortOrder = "date(" + GrappboxContract.AdvancementEntry.COLUMN_ADVANCEMENT_DATE + "DESC";
        String selection;
        String[] selectionArgs;

        selection = GrappboxContract.AdvancementEntry.TABLE_NAME + "." + GrappboxContract.AdvancementEntry.COLUMN_LOCAL_STATS_ID;
        return null;
    }

    @Override
    public void onLoadFinished(Loader<Cursor> loader, Cursor data) {

    }

    @Override
    public void onLoaderReset(Loader<Cursor> loader) {

    }
}

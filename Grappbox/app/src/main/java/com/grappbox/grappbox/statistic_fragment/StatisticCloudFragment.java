package com.grappbox.grappbox.statistic_fragment;

import android.database.Cursor;
import android.graphics.Color;
import android.os.Bundle;
import android.support.annotation.Nullable;
import android.support.v4.app.Fragment;
import android.support.v4.app.LoaderManager;
import android.support.v4.content.ContextCompat;
import android.support.v4.content.CursorLoader;
import android.support.v4.content.Loader;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import com.github.mikephil.charting.charts.PieChart;
import com.github.mikephil.charting.data.PieData;
import com.github.mikephil.charting.data.PieDataSet;
import com.github.mikephil.charting.data.PieEntry;
import com.grappbox.grappbox.ProjectActivity;
import com.grappbox.grappbox.R;
import com.grappbox.grappbox.data.GrappboxContract;

import java.util.ArrayList;

/**
 * Created by Arka on 02/01/2017.
 */

public class StatisticCloudFragment extends Fragment implements LoaderManager.LoaderCallbacks<Cursor> {

    public static final String LOG_TAG = StatisticCloudFragment.class.getSimpleName();
    private PieChart mChart;

    public StatisticCloudFragment() {
        super();
    }

    @Nullable
    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
        View v = inflater.inflate(R.layout.statistic_cloud_fragment, container, false);
        mChart = (PieChart)v.findViewById(R.id.sstorage_pie_chart);
        mChart.setUsePercentValues(true);
        mChart.getDescription().setEnabled(false);
        mChart.setExtraOffsets(5, 10, 5, 5);

        mChart.setDragDecelerationFrictionCoef(0.95f);

        mChart.setDrawHoleEnabled(false);

        mChart.setTransparentCircleColor(Color.WHITE);
        mChart.setTransparentCircleAlpha(110);
        mChart.setTransparentCircleRadius(61f);

        mChart.setDrawCenterText(true);
        mChart.setRotationAngle(0);
        getLoaderManager().initLoader(0, null, this);
        return v;
    }

    @Override
    public Loader<Cursor> onCreateLoader(int id, Bundle args) {
        long lpid = getActivity().getIntent().getLongExtra(ProjectActivity.EXTRA_PROJECT_ID, -1);
        String selection;
        String[] selectionArgs;

        final String[] projectionAdvancement = {
                GrappboxContract.StatEntry.TABLE_NAME + "." + GrappboxContract.StatEntry._ID,
                GrappboxContract.StatEntry.TABLE_NAME + "." + GrappboxContract.StatEntry.COLUMN_STORAGE_OCCUPIED,
                GrappboxContract.StatEntry.TABLE_NAME + "." + GrappboxContract.StatEntry.COLUMN_STORAGE_TOTAL,
        };
        selection = GrappboxContract.StatEntry.TABLE_NAME + "." + GrappboxContract.StatEntry.COLUMN_LOCAL_PROJECT_ID + "=?";
        selectionArgs = new String[] {String.valueOf(lpid)};

        return new CursorLoader(getActivity(), GrappboxContract.StatEntry.CONTENT_URI, projectionAdvancement, selection, selectionArgs, null);
    }

    @Override
    public void onLoadFinished(Loader<Cursor> loader, Cursor data) {
        if (data == null || !data.moveToFirst()) {
            Log.v(LOG_TAG, "data null");
            return;
        }
        ArrayList<Integer> colors = new ArrayList<>();
        colors.add(ContextCompat.getColor(getContext(), R.color.GrappRed));
        colors.add(ContextCompat.getColor(getContext(), R.color.GrappYellow));
        ArrayList<PieEntry> entries = new ArrayList<PieEntry>();
        entries.add(new PieEntry(data.getInt(data.getColumnIndex(GrappboxContract.StatEntry.COLUMN_STORAGE_OCCUPIED)), "Occupied"));
        entries.add(new PieEntry(data.getInt(data.getColumnIndex(GrappboxContract.StatEntry.COLUMN_STORAGE_TOTAL)), "Total"));
        PieDataSet dataSet = new PieDataSet(entries, "Cloud Storage");
        dataSet.setSliceSpace(3f);
        dataSet.setSelectionShift(5f);
        dataSet.setColors(colors);

        dataSet.setValueLinePart1OffsetPercentage(90.f);
        dataSet.setValueLinePart1Length(0.4f);
        dataSet.setValueLinePart2Length(0.6f);
        dataSet.setYValuePosition(PieDataSet.ValuePosition.OUTSIDE_SLICE);

        PieData pieData = new PieData(dataSet);
        pieData.setValueTextSize(12f);
        pieData.setValueTextColor(Color.BLACK);
        mChart.setData(pieData);

        // undo all highlights
        mChart.highlightValues(null);

        mChart.invalidate();


    }

    @Override
    public void onLoaderReset(Loader<Cursor> loader) {

    }
}

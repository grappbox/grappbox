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

import com.github.mikephil.charting.charts.LineChart;
import com.github.mikephil.charting.components.AxisBase;
import com.github.mikephil.charting.components.Legend;
import com.github.mikephil.charting.components.XAxis;
import com.github.mikephil.charting.components.YAxis;
import com.github.mikephil.charting.data.Entry;
import com.github.mikephil.charting.data.LineData;
import com.github.mikephil.charting.data.LineDataSet;
import com.github.mikephil.charting.formatter.IAxisValueFormatter;
import com.github.mikephil.charting.utils.ColorTemplate;
import com.grappbox.grappbox.ProjectActivity;
import com.grappbox.grappbox.R;
import com.grappbox.grappbox.data.GrappboxContract;
import com.grappbox.grappbox.model.StatAdvancementModel;

import java.text.ParseException;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Date;
import java.util.List;
import java.util.concurrent.TimeUnit;

/**
 * Created by tan_f on 30/12/2016.
 */

public class StatisticAdvancementFragment extends Fragment implements LoaderManager.LoaderCallbacks<Cursor> {

    public static final String LOG_TAG = StatisticAdvancementFragment.class.getSimpleName();
    private LineChart mChart;

    @Nullable
    @Override
    public View onCreateView(LayoutInflater inflater, @Nullable ViewGroup container, @Nullable Bundle savedInstanceState) {
        View v = inflater.inflate(R.layout.statistic_advancement_fragment, container, false);
        mChart = (LineChart) v.findViewById(R.id.advancement_line_chart);
        mChart.getDescription().setEnabled(false);
        mChart.setTouchEnabled(true);
        mChart.setDragDecelerationFrictionCoef(0.9f);
        mChart.setDragEnabled(true);
        mChart.setScaleEnabled(true);
        mChart.setDrawGridBackground(true);
        mChart.setHighlightPerDragEnabled(true);

        mChart.setBackgroundColor(ContextCompat.getColor(getContext(), R.color.GrappWhiteLight));
        getLoaderManager().initLoader(0, null, this);
        return v;
    }

    @Override
    public Loader<Cursor> onCreateLoader(int id, Bundle args) {
        long lpid = getActivity().getIntent().getLongExtra(ProjectActivity.EXTRA_PROJECT_ID, -1);
        String sortOrder = "date(" + GrappboxContract.AdvancementEntry.COLUMN_ADVANCEMENT_DATE + ") ASC";
        String selection;
        String[] selectionArgs;

        final String[] projectionAdvancement = {
                GrappboxContract.AdvancementEntry.TABLE_NAME + "." + GrappboxContract.AdvancementEntry._ID,
                GrappboxContract.AdvancementEntry.TABLE_NAME + "." + GrappboxContract.AdvancementEntry.COLUMN_ADVANCEMENT_DATE,
                GrappboxContract.AdvancementEntry.TABLE_NAME + "." + GrappboxContract.AdvancementEntry.COLUMN_PERCENTAGE,
                GrappboxContract.AdvancementEntry.TABLE_NAME + "." + GrappboxContract.AdvancementEntry.COLUMN_PROGRESS,
                GrappboxContract.AdvancementEntry.TABLE_NAME + "." + GrappboxContract.AdvancementEntry.COLUMN_FINISHED_TASk,
                GrappboxContract.AdvancementEntry.TABLE_NAME + "." + GrappboxContract.AdvancementEntry.COLUMN_TOTAL_TASK,
        };
        Cursor statId = getContext().getContentResolver().query(GrappboxContract.StatEntry.CONTENT_URI, new String[] {GrappboxContract.StatEntry._ID}, GrappboxContract.StatEntry.COLUMN_LOCAL_PROJECT_ID + "=?", new String[]{String.valueOf(lpid)}, null);
        if (statId == null || !statId.moveToFirst())
            return  null;
        selection = GrappboxContract.AdvancementEntry.TABLE_NAME + "." + GrappboxContract.AdvancementEntry.COLUMN_LOCAL_STATS_ID + "=?";
        selectionArgs = new String[] {String.valueOf(statId.getLong(0))};
        statId.close();
        return new CursorLoader(getActivity(), GrappboxContract.AdvancementEntry.CONTENT_URI, projectionAdvancement, selection, selectionArgs, sortOrder);
    }

    @Override
    public void onLoadFinished(Loader<Cursor> loader, Cursor data) {
        if (data == null || !data.moveToFirst()) {
            return;
        }
        List<StatAdvancementModel> models = new ArrayList<>();
        do {
            models.add(new StatAdvancementModel(getActivity(), data));
        } while (data.moveToNext());
        fillChart(models);
    }

    @Override
    public void onLoaderReset(Loader<Cursor> loader) {

    }

    private void fillChart(List<StatAdvancementModel> models) {
        SimpleDateFormat format = new SimpleDateFormat("yyyy-MM-dd HH:mm:ss");
        ArrayList<Entry> values = new ArrayList<>();

        try {
            for (StatAdvancementModel model : models)
            {
                Date date = format.parse(model._date);
                values.add(new Entry(TimeUnit.MILLISECONDS.toHours(date.getTime()), model._percentage));
                Log.v(LOG_TAG, "date : " + model._date);
            }

        } catch (ParseException e) {
            e.printStackTrace();
        }

        LineDataSet set = new LineDataSet(values, "Percentage");
        set.setAxisDependency(YAxis.AxisDependency.LEFT);
        set.setColor(ContextCompat.getColor(getContext(), R.color.GrappRed));
        set.setValueTextColor(ColorTemplate.getHoloBlue());
        set.setLineWidth(2f);
        set.setDrawCircles(false);
        set.setValueTextSize(9f);
        set.setFillAlpha(65);
        set.setFillColor(ContextCompat.getColor(getContext(), R.color.GrappRed));
        set.setHighLightColor(Color.rgb(244, 117, 117));
        set.setDrawCircleHole(false);

        LineData data = new LineData(set);
        data.setValueTextColor(Color.WHITE);
        data.setValueTextSize(9f);

        mChart.setData(data);
        mChart.invalidate();

        Legend l = mChart.getLegend();
        l.setEnabled(true);

        XAxis xAxis = mChart.getXAxis();
        xAxis.setPosition(XAxis.XAxisPosition.BOTTOM);
        xAxis.setTextSize(10f);
        xAxis.setDrawAxisLine(true);
        xAxis.setDrawGridLines(true);
        xAxis.setTextColor(Color.BLACK);
        xAxis.setGranularity(1f); // one hour
        xAxis.setValueFormatter(new IAxisValueFormatter() {

            private SimpleDateFormat mFormat = new SimpleDateFormat("dd MMM");

            @Override
            public String getFormattedValue(float value, AxisBase axis) {

                long millis = TimeUnit.HOURS.toMillis((long) value);
                return mFormat.format(new Date(millis));
            }
        });

        YAxis leftAxis = mChart.getAxisLeft();
        leftAxis.setPosition(YAxis.YAxisLabelPosition.OUTSIDE_CHART);
        leftAxis.setTextColor(ColorTemplate.getHoloBlue());
        leftAxis.setDrawGridLines(true);
        leftAxis.setGranularityEnabled(true);
        leftAxis.setAxisMinimum(-10f);
        leftAxis.setAxisMaximum(110f);
        leftAxis.setYOffset(-9f);
        leftAxis.setTextColor(Color.BLACK);

        YAxis rightAxis = mChart.getAxisRight();
        rightAxis.setEnabled(false);
    }
}

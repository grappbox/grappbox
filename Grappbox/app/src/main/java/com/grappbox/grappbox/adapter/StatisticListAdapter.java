package com.grappbox.grappbox.adapter;

import android.app.Activity;
import android.support.v7.widget.RecyclerView;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.TextView;

import com.github.mikephil.charting.charts.LineChart;
import com.grappbox.grappbox.R;
import com.grappbox.grappbox.model.StatLineChartModel;

import java.util.ArrayList;
import java.util.List;

/**
 * Created by tan_f on 30/12/2016.
 */

public class StatisticListAdapter extends RecyclerView.Adapter<RecyclerView.ViewHolder> {

    private static final int STAT_ADVANCEMENT = 0;
    private static final int STAT_ADVANCEMENT_TASK = 1;
    private static final int STAT_LATE_TASK = 2;
    private static final int STAT_USER_WORKING_CHARGE = 3;

    private Activity mContext;
    private LayoutInflater mInflater;

    public StatisticListAdapter(Activity context) {
        super();
        mContext = context;
        mInflater = LayoutInflater.from(context);
    }

    @Override
    public void onBindViewHolder(RecyclerView.ViewHolder holder, int position, List<Object> payloads) {
        super.onBindViewHolder(holder, position, payloads);
    }

    private void bindStatAdvancementEntry(final StatLineChartModel model, final StatAdvancementHolder holder, final int position) {

    }

    @Override
    public void onBindViewHolder(RecyclerView.ViewHolder holder, int position) {
        switch (getItemViewType(position)) {
            case STAT_ADVANCEMENT:
                break;
        }
    }

    @Override
    public int getItemCount() {
        return 0;
    }

    private RecyclerView.ViewHolder createStatLineChartHolder(ViewGroup parent) {
        final StatAdvancementHolder holder = new StatAdvancementHolder(mInflater.inflate(R.layout.list_item_stat_line_chart, parent, false));
        return holder;
    }

    public RecyclerView.ViewHolder onCreateViewHolder(ViewGroup parent, int viewType) {
        switch (viewType) {
            case STAT_ADVANCEMENT:
                return createStatLineChartHolder(parent);
        }
        return null;
    }

    private static class StatAdvancementHolder extends RecyclerView.ViewHolder {

        LineChart lineChart;

        public StatAdvancementHolder(View itemView) {
            super(itemView);
            lineChart = (LineChart) itemView.findViewById(R.id.line_chart);
        }
    }

}

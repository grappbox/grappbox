package com.grappbox.grappbox.adapter;

import android.app.Activity;
import android.support.v7.widget.RecyclerView;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.TextView;

import com.grappbox.grappbox.R;
import com.grappbox.grappbox.model.CalendarEventModel;

import java.text.ParseException;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Collection;
import java.util.List;

/**
 * Created by tan_f on 11/11/2016.
 */

public class CalendarEventAdapter extends RecyclerView.Adapter<RecyclerView.ViewHolder> {

    public static final int TYPE_EVENT_ENTRY = 0;

    private SimpleDateFormat format = new SimpleDateFormat("hh:mm");
    private SimpleDateFormat mDateFormat = new SimpleDateFormat("yyyy-MM-dd hh:mm:ss");
    private Activity mContext;
    private LayoutInflater inflater;
    private List<CalendarEventModel> mDataSet;

    public CalendarEventAdapter(Activity context) {
        super();
        mContext = context;
        mDataSet = new ArrayList<>();
        inflater = LayoutInflater.from(mContext);
    }

    public void add(CalendarEventModel item){
        mDataSet.add(item);
        notifyDataSetChanged();
    }

    public void add(Collection<? extends CalendarEventModel> items) {
        mDataSet.addAll(items);
        notifyDataSetChanged();
    }

    private RecyclerView.ViewHolder createEventEntryHolder(ViewGroup parent) {
        final CalendarEventHolder holder = new CalendarEventHolder(inflater.inflate(R.layout.list_item_calendar, parent, false), parent);
        return holder;
    }

    public RecyclerView.ViewHolder onCreateViewHolder(ViewGroup parent, int viewType) {
        switch (viewType) {
            case TYPE_EVENT_ENTRY :
                return createEventEntryHolder(parent);
        }
        return null;
    }

    @Override
    public void onBindViewHolder(RecyclerView.ViewHolder holder, int position, List<Object> payloads) {
        super.onBindViewHolder(holder, position, payloads);
    }

    private void bindEventEntry(final CalendarEventModel item, final CalendarEventHolder holder) {
        try {
            holder.mTitle.setText(item._title);
            holder.mBegin.setText(format.format(mDateFormat.parse(item._beginDate)));
            holder.mEnd.setText(format.format(mDateFormat.parse(item._endDate)));
        } catch (ParseException e) {
            e.printStackTrace();
        }
    }

    @Override
    public void onBindViewHolder(RecyclerView.ViewHolder holder, int position) {
        switch (getItemViewType(position)) {
            case TYPE_EVENT_ENTRY:
                bindEventEntry(getItemAt(position), (CalendarEventHolder)holder);
        }
    }

    public CalendarEventModel getItemAt(int position){
        return mDataSet.get(position);
    }

    @Override
    public int getItemCount() {
        return mDataSet.size();
    }

    private static class CalendarEventHolder extends RecyclerView.ViewHolder {

        TextView mBegin;
        TextView mEnd;
        TextView mTitle;

        public CalendarEventHolder(View itemView, ViewGroup root) {
            super(itemView);
            mBegin = (TextView) itemView.findViewById(R.id.event_begin);
            mEnd = (TextView) itemView.findViewById(R.id.event_end);
            mTitle = (TextView) itemView.findViewById(R.id.title);
        }
    }
}

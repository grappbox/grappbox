package com.grappbox.grappbox.adapter;

import android.content.Context;
import android.support.v7.widget.RecyclerView;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.TextView;

import com.grappbox.grappbox.R;
import com.grappbox.grappbox.model.NextMeetingModel;

import java.text.ParseException;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Collection;
import java.util.List;

/**
 * Created by tan_f on 10/01/2017.
 */

public class NextMeetingAdapter extends RecyclerView.Adapter<RecyclerView.ViewHolder> {

    private List<NextMeetingModel> mDataSet;

    private Context mContext;
    private LayoutInflater inflater;
    private static final SimpleDateFormat mDateFormat = new SimpleDateFormat("yyyy-MM-dd hh:mm:ss");
    private static final SimpleDateFormat date = new SimpleDateFormat("yyyy-MM-dd");
    private static final SimpleDateFormat hour = new SimpleDateFormat("hh:mm");

    public NextMeetingAdapter(Context context) {
        super();
        mContext = context;
        inflater = LayoutInflater.from(mContext);
        mDataSet = new ArrayList<>(0);
    }

    @Override
    public RecyclerView.ViewHolder onCreateViewHolder(ViewGroup parent, int viewType) {
        return new NextMeetingHolder(inflater.inflate(R.layout.list_item_next_meeting, parent, false));
    }

    public void add(NextMeetingModel item) {
        mDataSet.add(item);
        notifyDataSetChanged();
    }

    public void addAll(Collection<? extends NextMeetingModel> items) {
        mDataSet.addAll(items);
        notifyDataSetChanged();
    }

    @Override
    public void onBindViewHolder(RecyclerView.ViewHolder holder, int position) {

        try {
            NextMeetingHolder vh = (NextMeetingHolder) holder;
            NextMeetingModel item = mDataSet.get(position);
            vh.mTitle.setText(item._title);
            vh.mDesc.setText(item._desc);
            vh.mBeginDate.setText(date.format(mDateFormat.parse(item._date_begin)));
            vh.mEventBegin.setText(hour.format(mDateFormat.parse(item._date_begin)));
            vh.mEventEnd.setText(hour.format(mDateFormat.parse(item._date_end)));
        } catch (ParseException e) {
            e.printStackTrace();
        }

    }

    @Override
    public int getItemCount() {
        return mDataSet.size();
    }

    public void clear() { mDataSet.clear(); }

    public static class NextMeetingHolder extends  RecyclerView.ViewHolder {

        TextView mTitle;
        TextView mDesc;
        TextView mBeginDate;
        TextView mEventBegin;
        TextView mEventEnd;


        public NextMeetingHolder(View itemView) {
            super(itemView);
            mTitle = (TextView) itemView.findViewById(R.id.title);
            mDesc = (TextView) itemView.findViewById(R.id.description);
            mBeginDate = (TextView) itemView.findViewById(R.id.date_begin);
            mEventBegin = (TextView) itemView.findViewById(R.id.event_begin);
            mEventEnd = (TextView) itemView.findViewById(R.id.event_end);
        }
    }
}

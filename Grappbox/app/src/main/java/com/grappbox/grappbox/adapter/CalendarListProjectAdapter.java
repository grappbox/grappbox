package com.grappbox.grappbox.adapter;

import android.app.Activity;
import android.support.v7.widget.RecyclerView;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;
import android.widget.TextView;

import com.grappbox.grappbox.R;
import com.grappbox.grappbox.model.CalendarProjectModel;
import com.grappbox.grappbox.model.TimelineModel;

import java.util.ArrayList;
import java.util.Collection;
import java.util.List;

/**
 * Created by tan_f on 06/11/2016.
 */

public class CalendarListProjectAdapter extends RecyclerView.Adapter<RecyclerView.ViewHolder> {

    private static final int CALENDAR_PROJECT = 0;

    private Activity mContext;
    private LayoutInflater inflater;
    private List<CalendarProjectModel> mDataSet = new ArrayList<>();

    public CalendarListProjectAdapter(Activity context) {
        super();
        mContext = context;
        inflater = LayoutInflater.from(mContext);
        mDataSet.add(new CalendarProjectModel());
    }

    public void setItem(Collection<? extends CalendarProjectModel> items){
        mDataSet.clear();
        notifyDataSetChanged();
        mDataSet.add(new CalendarProjectModel());
        notifyDataSetChanged();
        mDataSet.addAll(items);
        notifyDataSetChanged();
    }

    private RecyclerView.ViewHolder createCalendarProjectEntryHolder(ViewGroup parent) {
        final CalendarProjectHolder holder = new CalendarProjectHolder(inflater.inflate(R.layout.list_item_calendar_project, parent, false), parent);
        return holder;
    }

    @Override
    public RecyclerView.ViewHolder onCreateViewHolder(ViewGroup parent, int viewType) {
        switch (viewType){
            case CALENDAR_PROJECT:
                return createCalendarProjectEntryHolder(parent);
        }
        return null;
    }

    private void bindCalendarProject(CalendarProjectModel item, CalendarProjectHolder holder)
    {
        holder.mTitle.setText(item._projectName);
    }

    @Override
    public void onBindViewHolder(RecyclerView.ViewHolder holder, int position) {
        switch (getItemViewType(position)){
            case CALENDAR_PROJECT:
                if (position > 0)
                    bindCalendarProject(getItemAt(position), (CalendarProjectHolder) holder);
                break;
        }
    }

    public CalendarProjectModel getItemAt(int position){
        return mDataSet.get(position);
    }

    @Override
    public void onBindViewHolder(RecyclerView.ViewHolder holder, int position, List<Object> payloads) {
        super.onBindViewHolder(holder, position, payloads);
    }

    @Override
    public int getItemCount() {
        return mDataSet.size();
    }

    @Override
    public int getItemViewType(int position) {
        return CALENDAR_PROJECT;
    }

    public List<CalendarProjectModel> getDataSet() { return mDataSet; }

    private static class CalendarProjectHolder extends RecyclerView.ViewHolder {

        ImageView mLogo;
        TextView mTitle;
        ViewGroup mparent;

        public CalendarProjectHolder(View itemView, ViewGroup root){
            super(itemView);
            mLogo = (ImageView) itemView.findViewById(R.id.avatar);
            mTitle = (TextView) itemView.findViewById(R.id.title);
            mparent = root;

        }
    }

}

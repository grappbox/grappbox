package com.grappbox.grappbox.adapter;

import android.app.Activity;
import android.content.Intent;
import android.support.v7.widget.RecyclerView;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageButton;
import android.widget.ImageView;
import android.widget.TextView;

import com.grappbox.grappbox.R;
import com.grappbox.grappbox.model.TimelineModel;
import com.grappbox.grappbox.timeline_fragment.TimelineMessageCommentActivity;

import java.util.ArrayList;
import java.util.Collection;
import java.util.List;

/**
 * Created by tan_f on 28/09/2016.
 */

public class TimelineListAdapter extends RecyclerView.Adapter<RecyclerView.ViewHolder> {

    private Activity mContext;
    private List<TimelineModel> mDataset;
    private LayoutInflater  inflater;

    public static final int TYPE_TIMELINE_ENTRY = 0;

    public TimelineListAdapter(Activity context)
    {
        super();
        mContext = context;
        mDataset = new ArrayList<>(0);
        inflater = LayoutInflater.from(context);
    }

    private RecyclerView.ViewHolder createTimelineEntryHolder(ViewGroup parent){
        final TimelineHolder holder = new TimelineHolder(inflater.inflate(R.layout.list_item_timeline_list, parent, false), parent);
        return holder;
    }

    public void add(TimelineModel item){
        mDataset.add(item);
        notifyDataSetChanged();
    }

    public void add(Collection<? extends TimelineModel> items){
        mDataset.addAll(items);
        notifyDataSetChanged();
    }

    @Override
    public RecyclerView.ViewHolder onCreateViewHolder(ViewGroup parent, int viewType) {
        switch (viewType){
            case TYPE_TIMELINE_ENTRY:
                return createTimelineEntryHolder(parent);
        }
        return null;
    }

    public void bindTimelineEntry(final TimelineModel item, TimelineHolder holder){
        holder.mTitle.setText(item._title);
        holder.mMessage.setText(item._message);
        holder.mLastUpdate.setText(item._lastUpadte);
        holder.mAnswer.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                Intent launchMessageComment = new Intent(mContext, TimelineMessageCommentActivity.class);
                launchMessageComment.putExtra(TimelineMessageCommentActivity.EXTRA_TIMELINE_MODEL, item);
                TimelineModel model = launchMessageComment.getParcelableExtra(TimelineMessageCommentActivity.EXTRA_TIMELINE_MODEL);
                mContext.startActivity(launchMessageComment);
            }
        });
    }

    @Override
    public void onBindViewHolder(RecyclerView.ViewHolder holder, int position) {
        switch (getItemViewType(position)){
            case TYPE_TIMELINE_ENTRY:
                bindTimelineEntry(getItemAt(position), (TimelineHolder) holder);
                break;
        }
    }

    public TimelineModel getItemAt(int position){
        return mDataset.get(position);
    }

    @Override
    public void onBindViewHolder(RecyclerView.ViewHolder holder, int position, List<Object> payloads) {
        super.onBindViewHolder(holder, position, payloads);
    }

    @Override
    public int getItemViewType(int position) {
        return TYPE_TIMELINE_ENTRY;
    }

    @Override
    public int getItemCount() {
        return mDataset.size();
    }

    public boolean isEmpty(){
        return mDataset.isEmpty();
    }

    public void clear(){
        mDataset.clear();
        notifyDataSetChanged();
    }

    private static class TimelineHolder extends RecyclerView.ViewHolder {

        ImageView   mAvatar;
        TextView    mTitle;
        TextView    mMessage;
        TextView    mLastUpdate;
        ImageButton mAnswer;
        ImageButton mEdit;
        ImageButton mDelete;
        ViewGroup   mparent;

        public TimelineHolder(View itemView, ViewGroup root){
            super(itemView);
            mAvatar = (ImageView) itemView.findViewById(R.id.avatar);
            mTitle = (TextView) itemView.findViewById(R.id.title);
            mMessage = (TextView) itemView.findViewById(R.id.messagecontent);
            mLastUpdate = (TextView) itemView.findViewById(R.id.messagelastupdate);
            mAnswer = (ImageButton) itemView.findViewById(R.id.answer);
            mEdit = (ImageButton) itemView.findViewById(R.id.edit);
            mDelete = (ImageButton) itemView.findViewById(R.id.delete);
            mparent = root;

        }
    }
}

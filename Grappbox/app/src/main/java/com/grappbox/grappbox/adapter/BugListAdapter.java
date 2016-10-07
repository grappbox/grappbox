package com.grappbox.grappbox.adapter;

import android.app.Activity;
import android.content.Intent;
import android.support.v4.util.Pair;
import android.support.v7.widget.RecyclerView;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.LinearLayout;
import android.widget.TextView;

import com.grappbox.grappbox.R;
import com.grappbox.grappbox.bugtracker_fragments.BugDetailsActivity;
import com.grappbox.grappbox.model.BugModel;

import java.util.ArrayList;
import java.util.Collection;
import java.util.List;

/**
 * Created by marcw on 17/09/2016.
 */
public class BugListAdapter extends RecyclerView.Adapter<RecyclerView.ViewHolder> {
    boolean showLoadingMore = false;

    private Activity mContext;
    private List<BugModel> mDataset;
    private LayoutInflater inflater;

    public static final int TYPE_BUG_ENTRY = 0;

    public BugListAdapter(Activity context){
        super();
        mContext = context;
        mDataset = new ArrayList<>(0);
        inflater = LayoutInflater.from(context);
    }

    private RecyclerView.ViewHolder createBugEntryHolder(ViewGroup parent){
        final BugHolder holder = new BugHolder(inflater.inflate(R.layout.list_item_bugtracker_list, parent, false), parent);

        return holder;
    }


    public void add(BugModel item){
        mDataset.add(item);
        notifyDataSetChanged();
    }

    public void add(Collection<? extends BugModel> items){
        mDataset.addAll(items);
        notifyDataSetChanged();
    }

    @Override
    public RecyclerView.ViewHolder onCreateViewHolder(ViewGroup parent, int viewType) {
        switch (viewType){
            case TYPE_BUG_ENTRY:
                return createBugEntryHolder(parent);
        }
        return null;
    }

    public void bindBugEntry(final BugModel item, BugHolder holder){
        boolean tagPassed = false;
        holder.title.setText(item.title);
        holder.desc.setText(item.desc);
        holder.assignee.setText(String.valueOf(item.assigneeCount));
        holder.comments.setText(String.valueOf(item.commentsCount));

        for (Pair<String, String> tag : item.tags){
            tagPassed = true;
            View tagView = inflater.inflate(R.layout.list_item_bugtracker_tagitem, holder.parent, false);
            ((TextView)tagView.findViewById(R.id.tagname)).setText(tag.first);
            holder.tagContainer.addView(tagView);
        }
        holder.itemView.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                Intent launchDetail = new Intent(mContext, BugDetailsActivity.class);
                launchDetail.putExtra(BugDetailsActivity.EXTRA_BUG_MODEL, item);
                mContext.startActivity(launchDetail);
            }
        });
        holder.tagContainer.setVisibility(tagPassed ? View.VISIBLE : View.GONE);
    }

    @Override
    public void onBindViewHolder(RecyclerView.ViewHolder holder, int position) {
        switch(getItemViewType(position)){
            case TYPE_BUG_ENTRY:
                bindBugEntry(getItemAt(position), (BugHolder) holder);
                break;
        }
    }

    public BugModel getItemAt(int position){
        return mDataset.get(position);
    }

    @Override
    public int getItemViewType(int position){
        return TYPE_BUG_ENTRY;
    }

    @Override
    public int getItemCount() {
        return mDataset.size();
    }

    public boolean isEmpty() {
        return mDataset.isEmpty();
    }

    public void clear() {
        mDataset.clear();
        notifyDataSetChanged();
    }

    private static class BugHolder extends RecyclerView.ViewHolder{
        TextView title, desc, assignee, comments;
        LinearLayout tagContainer;
        ViewGroup parent;

         BugHolder(View itemView, ViewGroup root) {
            super(itemView);
            title = (TextView) itemView.findViewById(R.id.title);
            desc = (TextView) itemView.findViewById(R.id.date_status);
            assignee = (TextView) itemView.findViewById(R.id.nb_assignees);
            comments = (TextView) itemView.findViewById(R.id.nb_comments);
            tagContainer = (LinearLayout) itemView.findViewById(R.id.tag_container);
            parent = root;
        }
    }

}

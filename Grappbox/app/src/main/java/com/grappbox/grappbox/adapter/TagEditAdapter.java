package com.grappbox.grappbox.adapter;

import android.content.Context;
import android.content.res.ColorStateList;
import android.graphics.Color;
import android.os.Build;
import android.support.annotation.IntegerRes;
import android.support.v7.widget.RecyclerView;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageButton;
import android.widget.TextView;

import com.grappbox.grappbox.R;
import com.grappbox.grappbox.model.BugTagModel;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;

/**
 * Created by marc on 13/10/2016.
 */

public class TagEditAdapter extends RecyclerView.Adapter<RecyclerView.ViewHolder> {
    private List<BugTagModel> mTags;
    private List<BugTagModel> mDeleted;
    private Context mContext;
    private LayoutInflater mInflater;
    private Map<Integer, View> mViewItems;

    public TagEditAdapter(Context context) {
        mTags = new ArrayList<>();
        mContext = context;
        mInflater = LayoutInflater.from(context);
        mViewItems = new HashMap<>();
        mDeleted = new ArrayList<>();
    }

    @Override
    public RecyclerView.ViewHolder onCreateViewHolder(ViewGroup parent, int viewType) {
        return new TagViewHolder(mInflater.inflate(R.layout.view_tagedit, parent, false));
    }

    public List<BugTagModel> getDeletedTags(){return mDeleted;}

    public void setDataset(List<BugTagModel> mTags){
        this.mTags = mTags;
        notifyDataSetChanged();
    }

    public List<BugTagModel> getDataset(){
        return mTags;
    }

    @Override
    public void onBindViewHolder(final RecyclerView.ViewHolder holder, int position) {
        TagViewHolder vh = (TagViewHolder) holder;
        final BugTagModel model = mTags.get(position);
        vh.tagname.setText(model.name);
        vh.itemView.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                mDeleted.add(model);
                mTags.remove(holder.getAdapterPosition());
                notifyDataSetChanged();
            }
        });
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.LOLLIPOP)
            vh.itemView.setBackgroundTintList(ColorStateList.valueOf(Color.parseColor(model.color)));
        mViewItems.put(position, vh.itemView);
    }

    public View getViewAt(int i){
        return mViewItems.get(i);
    }

    @Override
    public int getItemCount() {
        return mTags == null ? 0 : mTags.size();
    }

    public void addTag(BugTagModel model) {
        mTags.add(model);
        mDeleted.remove(model);
        notifyItemInserted(mTags.size());
    }

    private static class TagViewHolder extends RecyclerView.ViewHolder{
        public TextView tagname;
        public ImageButton action_delete;

        public TagViewHolder(View itemView) {
            super(itemView);
            tagname = (TextView) itemView.findViewById(R.id.tagname);
            action_delete = (ImageButton) itemView.findViewById(R.id.delete);
        }
    }
}

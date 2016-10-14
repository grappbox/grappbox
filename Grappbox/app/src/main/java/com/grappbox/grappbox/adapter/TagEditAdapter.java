package com.grappbox.grappbox.adapter;

import android.content.Context;
import android.content.res.ColorStateList;
import android.os.Build;
import android.support.v7.widget.RecyclerView;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageButton;
import android.widget.TextView;

import com.grappbox.grappbox.R;
import com.grappbox.grappbox.model.BugTagModel;

import java.util.ArrayList;
import java.util.List;

/**
 * Created by marc on 13/10/2016.
 */

public class TagEditAdapter extends RecyclerView.Adapter<RecyclerView.ViewHolder> {
    List<BugTagModel> mTags;
    Context mContext;
    LayoutInflater mInflater;

    public TagEditAdapter(Context context) {
        this.mTags = new ArrayList<>();
        mContext = context;
        mInflater = LayoutInflater.from(context);
    }

    @Override
    public RecyclerView.ViewHolder onCreateViewHolder(ViewGroup parent, int viewType) {
        return new TagViewHolder(mInflater.inflate(R.layout.view_tagedit, parent, false));
    }

    public void setDataset(List<BugTagModel> mTags){
        this.mTags = mTags;
    }

    public List<BugTagModel> getDataset(){
        return mTags;
    }

    @Override
    public void onBindViewHolder(final RecyclerView.ViewHolder holder, int position) {
        TagViewHolder vh = (TagViewHolder) holder;
        BugTagModel model = mTags.get(position);
        vh.tagname.setText(model.name);
        vh.action_delete.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                mTags.remove(holder.getAdapterPosition());
                notifyDataSetChanged();
            }
        });
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.LOLLIPOP)
            vh.itemView.setBackgroundTintList(ColorStateList.valueOf(Integer.valueOf(model.color)));
    }

    @Override
    public int getItemCount() {
        return mTags.size();
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

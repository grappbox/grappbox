package com.grappbox.grappbox.adapter;

import android.content.Context;
import android.support.v7.widget.RecyclerView;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;
import android.widget.TextView;

import com.grappbox.grappbox.R;
import com.grappbox.grappbox.model.UserModel;

import java.util.ArrayList;
import java.util.List;

/**
 * Created by marc on 13/10/2016.
 */

public class AssigneeEditAdapter extends RecyclerView.Adapter<RecyclerView.ViewHolder> {
    List<UserModel> mDataset;
    Context mContext;
    LayoutInflater mInflater;

    public AssigneeEditAdapter(Context context) {
        mDataset = new ArrayList<>();
        mContext = context;
        mInflater = LayoutInflater.from(context);
    }

    @Override
    public RecyclerView.ViewHolder onCreateViewHolder(ViewGroup parent, int viewType) {
        return new AssigneeHolder(mInflater.inflate(R.layout.bug_assignee, parent, false));
    }

    public void setDataset(List<UserModel> dataset){
        mDataset = dataset;
    }

    public List<UserModel> getDataset(){
        return  mDataset;
    }

    @Override
    public void onBindViewHolder(RecyclerView.ViewHolder holder, int position) {
        AssigneeHolder vh = (AssigneeHolder) holder;
        UserModel data = mDataset.get(position);
        vh.username.setText(data.mFirstname + " " + data.mLastname);
    }

    @Override
    public int getItemCount() {
        return mDataset.size();
    }

    private static class AssigneeHolder extends RecyclerView.ViewHolder{
        public ImageView avatar;
        public TextView username;

        public AssigneeHolder(View itemView) {
            super(itemView);
            avatar = (ImageView) itemView.findViewById(R.id.avatar);
            username = (TextView) itemView.findViewById(R.id.username);
        }
    }
}

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
 * Created by tan_f on 07/11/2016.
 */

public class CalendarParticipantAdapter  extends RecyclerView.Adapter<RecyclerView.ViewHolder> {

    List<UserModel> mDataSet;
    Context mContext;
    LayoutInflater mInflater;

    public CalendarParticipantAdapter(Context context) {
        mContext = context;
        mDataSet = new ArrayList<>();
        mInflater = LayoutInflater.from(context);
    }

    @Override
    public RecyclerView.ViewHolder onCreateViewHolder(ViewGroup parent, int viewType) {
        return new ParticipantsHolder(mInflater.inflate(R.layout.list_item_calendar_participant, parent, false));
    }

    public void setDataSet(List<UserModel> dataset){
        mDataSet = dataset;
        notifyDataSetChanged();
    }

    public List<UserModel> getDataSet() { return mDataSet; }

    public void clear() {
        mDataSet.clear();
        notifyDataSetChanged();
    }

    @Override
    public void onBindViewHolder(RecyclerView.ViewHolder holder, int position) {
        ParticipantsHolder vh = (ParticipantsHolder)holder;
        UserModel data = mDataSet.get(position);
        vh.username.setText(data.mFirstname + " " + data.mLastname);
    }

    @Override
    public int getItemCount() {
        return mDataSet.size();
    }

    private static class ParticipantsHolder extends RecyclerView.ViewHolder{
        public ImageView avatar;
        public TextView username;

        public ParticipantsHolder(View itemView) {
            super(itemView);
            avatar = (ImageView) itemView.findViewById(R.id.avatar);
            username = (TextView) itemView.findViewById(R.id.username);
        }

    }
}

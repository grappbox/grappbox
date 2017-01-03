package com.grappbox.grappbox.adapter;

import android.content.Context;
import android.support.v7.widget.RecyclerView;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;
import android.widget.TextView;

import com.grappbox.grappbox.R;
import com.grappbox.grappbox.model.CalendarEventModel;
import com.grappbox.grappbox.model.UserModel;

import java.util.ArrayList;
import java.util.List;

/**
 * Created by tan_f on 01/12/2016.
 */

public class CalendarDetailAdapter extends RecyclerView.Adapter<RecyclerView.ViewHolder> {

    private static final int TYPE_PARTICIPANT = 0;

    private List<UserModel> mParticipant;

    private CalendarEventModel mModel;
    private LayoutInflater mInflater;
    private Context mContext;

    public CalendarDetailAdapter(Context context) {
        mContext = context;
        mInflater = LayoutInflater.from(context);
        mParticipant = new ArrayList<>();
    }

    public void setEventModel(CalendarEventModel model) {
        mModel = model;
        mParticipant = model._user;
        notifyDataSetChanged();
    }

    @Override
    public RecyclerView.ViewHolder onCreateViewHolder(ViewGroup parent, int viewType) {
        switch (viewType) {

            case TYPE_PARTICIPANT:
                return new ParticipantHolder(mInflater.inflate(R.layout.list_item_calendar_participant, parent, false));

            default:
                throw new IllegalArgumentException("Bad viewType : " + viewType);
        }
    }

    @Override
    public int getItemViewType(int position) {
        return TYPE_PARTICIPANT;
    }

    @Override
    public void onBindViewHolder(RecyclerView.ViewHolder holder, int position, List<Object> payloads) {
        super.onBindViewHolder(holder, position, payloads);
    }

    private void bindParticipant(final ParticipantHolder holder, int position)
    {
        UserModel userModel = mParticipant.get(position);
        holder.mUsername.setText(userModel.toString());
    }

    @Override
    public void onBindViewHolder(RecyclerView.ViewHolder holder, int position) {
        switch (getItemViewType(position)) {

            case TYPE_PARTICIPANT:
                bindParticipant((ParticipantHolder)holder, position);
                break;

            default:
                throw new IllegalArgumentException("Bad viewType" + getItemViewType(position));
        }
    }

    @Override
    public int getItemCount() {
        return mParticipant.size();
    }

    private static class ParticipantHolder extends RecyclerView.ViewHolder {

        public ImageView mAvatar;
        public TextView mUsername;


        public ParticipantHolder(View itemView) {
            super(itemView);
            mUsername = (TextView) itemView.findViewById(R.id.username);
            mAvatar = (ImageView) itemView.findViewById(R.id.avatar);
        }
    }
}

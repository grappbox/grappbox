package com.grappbox.grappbox.adapter;

import android.content.Context;
import android.support.v7.widget.RecyclerView;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;
import android.widget.TextView;

import com.grappbox.grappbox.R;
import com.grappbox.grappbox.model.OccupationModel;

import java.util.ArrayList;
import java.util.Collection;
import java.util.List;

/**
 * Created by tan_f on 05/01/2017.
 */

public class TeamOccupationAdapter extends RecyclerView.Adapter<RecyclerView.ViewHolder> {

    private List<OccupationModel> mDataSet;

    private Context mContext;
    private LayoutInflater inflater;

    public TeamOccupationAdapter(Context context) {
        mContext = context;
        inflater = LayoutInflater.from(mContext);
        mDataSet = new ArrayList<>(0);
    }

    @Override
    public RecyclerView.ViewHolder onCreateViewHolder(ViewGroup parent, int viewType) {
        return new OccupationHolder(inflater.inflate(R.layout.list_item_occupation, parent, false));
    }

    public void add(OccupationModel item) {
        mDataSet.add(item);
        notifyDataSetChanged();
    }

    public void addAll(Collection<? extends  OccupationModel> items) {
        mDataSet.addAll(items);
        notifyDataSetChanged();
    }

    @Override
    public void onBindViewHolder(RecyclerView.ViewHolder holder, int position) {
        OccupationHolder vh = (OccupationHolder) holder;
        OccupationModel item = mDataSet.get(position);
        vh.mUserName.setText(item._userFirstName + " " + item._userLastName);
        vh.mIsBusy.setText(item._isBusy == 0 ? R.string.occupation_free : R.string.occupation_busy);
        vh.mTaskBegun.setText(String.valueOf(item._taskBegun));
        vh.mTaskOnGoing.setText(String.valueOf(item._taskOnGoing));
    }

    @Override
    public int getItemCount() {
        return mDataSet.size();
    }

    public void clear() { mDataSet.clear(); }

    private static class OccupationHolder extends RecyclerView.ViewHolder{

        ImageView mAvatar;
        TextView mIsBusy;
        TextView mTaskBegun;
        TextView mTaskOnGoing;
        TextView mUserName;

        public OccupationHolder(View itemView) {
            super(itemView);
            mAvatar = (ImageView) itemView.findViewById(R.id.avatar);
            mIsBusy = (TextView) itemView.findViewById(R.id.occupation_status);
            mTaskBegun = (TextView) itemView.findViewById(R.id.task_begun);
            mTaskOnGoing = (TextView) itemView.findViewById(R.id.task_on_going);
            mUserName = (TextView) itemView.findViewById(R.id.occupation_user_name);
        }


    }
}

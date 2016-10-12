package com.grappbox.grappbox.adapter;

import android.content.Context;
import android.support.design.widget.TextInputEditText;
import android.support.v7.widget.RecyclerView;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageButton;
import android.widget.ImageView;
import android.widget.TextView;

import com.grappbox.grappbox.R;
import com.grappbox.grappbox.model.TimelineMessageCommentModel;
import com.grappbox.grappbox.model.TimelineModel;

import java.util.ArrayList;
import java.util.List;

public class TimelineMessageCommentAdapter extends RecyclerView.Adapter<RecyclerView.ViewHolder> {

    private static final String LOG_TAG = TimelineMessageCommentAdapter.class.getSimpleName();

    private static final int    TYPE_MESSAGE_COMMENTS = 0;
    private static final int    TYPE_MESSAGE_REPLY = 1;

    private List<TimelineMessageCommentModel>   mComments;

    private LayoutInflater  mInflater;
    private Context         mContext;

    public TimelineMessageCommentAdapter(Context context) {
        mContext = context;
        mInflater = LayoutInflater.from(context);
        mComments = new ArrayList<>();
    }

    public void setTimelineModel(TimelineModel model){
        mComments = model.mComments;
        notifyDataSetChanged();
    }

    private RecyclerView.ViewHolder createCommentViewHolder(ViewGroup parent){
        RecyclerView.ViewHolder vh = new TimelineMessageCommentHolder(mInflater.inflate(R.layout.list_item_timeline_message_comment_list, parent, false), parent);
        return vh;
    }

    public RecyclerView.ViewHolder onCreateViewHolder(ViewGroup parent, int viewType) {
        switch (viewType){
            case TYPE_MESSAGE_COMMENTS:
                return createCommentViewHolder(parent);
            case TYPE_MESSAGE_REPLY:
                return new CommentReplyHolder(mInflater.inflate(R.layout.list_item_timeline_message_comment_reply, parent, false));
            default:
                throw new IllegalArgumentException("Bad viewTYpe : " + viewType);
        }
    }

    @Override
    public int getItemViewType(int position) {
        if (position == getItemCount() - 1)
            return TYPE_MESSAGE_REPLY;
        return TYPE_MESSAGE_COMMENTS;
    }

    private void bindCommen(TimelineMessageCommentHolder holder, TimelineMessageCommentModel data){
        holder.mMessage.setText(data._comment);
        holder.mLastUpdate.setText(data._lastupdate);
    }

    @Override
    public void onBindViewHolder(RecyclerView.ViewHolder holder, int position) {
        switch (getItemViewType(position)){
            case TYPE_MESSAGE_COMMENTS :
                bindCommen((TimelineMessageCommentHolder) holder, mComments.get(position));
                break;
            case TYPE_MESSAGE_REPLY :
                break;
            default :
                throw new IllegalArgumentException("Bad viewType : " + getItemViewType(position));
        }
    }

    @Override
    public int getItemCount() {
        return mComments.size() + 1;
    }

    private static class TimelineMessageCommentHolder extends RecyclerView.ViewHolder {

        ImageView mAvatar;
        TextView mTitle;
        TextView    mMessage;
        TextView    mLastUpdate;
        ImageButton mEdit;
        ImageButton mDelete;
        ViewGroup   mparent;

        public TimelineMessageCommentHolder(View itemView, ViewGroup root){
            super(itemView);
            mAvatar = (ImageView) itemView.findViewById(R.id.avatar);
            mTitle = (TextView) itemView.findViewById(R.id.title);
            mMessage = (TextView) itemView.findViewById(R.id.messagecontent);
            mLastUpdate = (TextView) itemView.findViewById(R.id.messagelastupdate);
            mEdit = (ImageButton) itemView.findViewById(R.id.edit);
            mDelete = (ImageButton) itemView.findViewById(R.id.delete);
            mparent = root;

        }
    }

    private static class CommentReplyHolder extends RecyclerView.ViewHolder{

        public ImageView avatar;
        public ImageButton send;
        public TextInputEditText comment;

        public CommentReplyHolder(View itemView){
            super(itemView);
            avatar = (ImageView) itemView.findViewById(R.id.avatar);
            send = (ImageButton) itemView.findViewById(R.id.reply);
            comment = (TextInputEditText) itemView.findViewById(R.id.comment);
        }
    }
}

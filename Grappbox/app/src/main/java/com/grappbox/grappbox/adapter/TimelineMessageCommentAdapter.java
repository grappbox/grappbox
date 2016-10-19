package com.grappbox.grappbox.adapter;

import android.accounts.AccountManager;
import android.app.Activity;
import android.content.Intent;
import android.database.Cursor;
import android.graphics.Color;
import android.support.design.widget.TextInputEditText;
import android.support.v4.content.ContextCompat;
import android.support.v7.widget.CardView;
import android.support.v7.widget.RecyclerView;
import android.util.Log;
import android.view.Gravity;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageButton;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.TextView;
import android.widget.Toast;

import com.grappbox.grappbox.R;
import com.grappbox.grappbox.data.GrappboxContract;
import com.grappbox.grappbox.model.TimelineMessageCommentModel;
import com.grappbox.grappbox.model.TimelineModel;
import com.grappbox.grappbox.receiver.RefreshReceiver;
import com.grappbox.grappbox.singleton.Session;
import com.grappbox.grappbox.sync.GrappboxJustInTimeService;

import java.util.ArrayList;
import java.util.Collection;
import java.util.List;

public class TimelineMessageCommentAdapter extends RecyclerView.Adapter<RecyclerView.ViewHolder> {

    private static final String LOG_TAG = TimelineMessageCommentAdapter.class.getSimpleName();

    private static final int    TYPE_MESSAGE_COMMENTS = 0;
    private static final int    TYPE_MESSAGE_REPLY = 1;

    private List<TimelineMessageCommentModel>   mComments;
    private TimelineModel                       mParent;
    private RefreshReceiver                     mRefreshReceiver;

    private LayoutInflater  mInflater;
    private Activity mContext;

    public TimelineMessageCommentAdapter(Activity context) {
        mContext = context;
        mInflater = LayoutInflater.from(context);
        mComments = new ArrayList<>();
    }

    public void setTimelineModel(TimelineModel model){
        mParent = model;
    }

    public void setRefreshReceiver(RefreshReceiver rf){
        mRefreshReceiver = rf;
    }

    public void add(TimelineMessageCommentModel comment){
        mComments.add(comment);
        notifyDataSetChanged();
    }

    public void add(Collection<? extends TimelineMessageCommentModel> models){
        mComments.addAll(models);
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

    private void bindComment(TimelineMessageCommentHolder holder, TimelineMessageCommentModel data){
        AccountManager am = AccountManager.get(mContext);
        long uid = Long.parseLong(am.getUserData(Session.getInstance(mContext).getCurrentAccount(), GrappboxJustInTimeService.EXTRA_USER_ID));
        Cursor cursorUserId = mContext.getContentResolver().query(GrappboxContract.UserEntry.CONTENT_URI,
                new String[] {GrappboxContract.UserEntry.TABLE_NAME + "." + GrappboxContract.UserEntry.COLUMN_GRAPPBOX_ID},
                GrappboxContract.UserEntry.TABLE_NAME + "." + GrappboxContract.UserEntry._ID + " =?",
                new String[]{String.valueOf(uid)},
                null);
        holder.mMessage.setText(data._comment);
        holder.mLastUpdate.setText(data._lastupdate);
        if (cursorUserId == null || !cursorUserId.moveToFirst())
            return ;
        Log.v(LOG_TAG, "created id : " + data._createId + ", User ID : " + cursorUserId.getLong(0) + ", cursor lenght : " + cursorUserId.getCount());
        if (Long.valueOf(data._createId) == cursorUserId.getLong(0))
        {
            holder.mlayout.setGravity(Gravity.END);
            holder.mAvatar.setVisibility(View.GONE);
            holder.mCardView.setCardBackgroundColor(ContextCompat.getColor(mContext, R.color.GrappBlue));
        }
        cursorUserId.close();
    }

    private void bindReply(final CommentReplyHolder holder)
    {
        holder.send.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                Toast.makeText(mContext, holder.comment.getText().toString(), Toast.LENGTH_SHORT).show();
                AccountManager am = AccountManager.get(mContext);
                final String token = am.getUserData(Session.getInstance(mContext).getCurrentAccount(), GrappboxJustInTimeService.EXTRA_API_TOKEN);
                Cursor cursorTimelineId = mContext.getContentResolver().query(GrappboxContract.TimelineMessageEntry.CONTENT_URI,
                        new String[] {GrappboxContract.TimelineEntry.TABLE_NAME + "." + GrappboxContract.TimelineEntry.COLUMN_GRAPPBOX_ID},
                        GrappboxContract.TimelineMessageEntry.TABLE_NAME + "." + GrappboxContract.TimelineMessageEntry.COLUMN_GRAPPBOX_ID + " =?",
                        new String[]{String.valueOf(mParent._grappboxId)},
                        null);

                if (cursorTimelineId == null || !cursorTimelineId.moveToFirst()) {
                    Log.v(LOG_TAG, "error cursor");
                    cursorTimelineId.close();
                    return;
                }

                Intent addComment = new Intent(mContext, GrappboxJustInTimeService.class);
                addComment.setAction(GrappboxJustInTimeService.ACTION_TIMELINE_ADD_MESSAGE);
                addComment.putExtra(GrappboxJustInTimeService.EXTRA_API_TOKEN, token);
                addComment.putExtra(GrappboxJustInTimeService.EXTRA_TIMELINE_MESSAGE, holder.comment.getText().toString());
                addComment.putExtra(GrappboxJustInTimeService.EXTRA_TIMELINE_TITLE, holder.comment.getText().toString());
                addComment.putExtra(GrappboxJustInTimeService.EXTRA_TIMELINE_PARENT_ID, Integer.valueOf(mParent._grappboxId));
                addComment.putExtra(GrappboxJustInTimeService.EXTRA_TIMELINE_ID, cursorTimelineId.getLong(0));
                addComment.putExtra(GrappboxJustInTimeService.EXTRA_RESPONSE_RECEIVER, mRefreshReceiver);
                mContext.startService(addComment);
                cursorTimelineId.close();
            }
        });
    }

    @Override
    public void onBindViewHolder(RecyclerView.ViewHolder holder, int position) {
        switch (getItemViewType(position)){
            case TYPE_MESSAGE_COMMENTS :
                bindComment((TimelineMessageCommentHolder) holder, mComments.get(position));
                break;
            case TYPE_MESSAGE_REPLY :
                bindReply((CommentReplyHolder) holder);
                break;
            default :
                throw new IllegalArgumentException("Bad viewType : " + getItemViewType(position));
        }
    }

    @Override
    public int getItemCount() {
        return  1 + mComments.size();
    }

    public void clear(){
        mComments.clear();
    }

    public int getSize() {return mComments.size();}

    private static class TimelineMessageCommentHolder extends RecyclerView.ViewHolder {

        ImageView mAvatar;
        TextView mTitle;
        TextView    mMessage;
        TextView    mLastUpdate;
//        ImageButton mEdit;
//        ImageButton mDelete;
        ViewGroup   mparent;
        CardView    mCardView;
        LinearLayout mlayout;


        public TimelineMessageCommentHolder(View itemView, ViewGroup root){
            super(itemView);
            mAvatar = (ImageView) itemView.findViewById(R.id.avatar);
            mTitle = (TextView) itemView.findViewById(R.id.title);
            mMessage = (TextView) itemView.findViewById(R.id.messagecontent);
            mLastUpdate = (TextView) itemView.findViewById(R.id.messagelastupdate);
//            mEdit = (ImageButton) itemView.findViewById(R.id.edit);
//            mDelete = (ImageButton) itemView.findViewById(R.id.delete);
            mlayout = (LinearLayout) itemView.findViewById(R.id.layoutcomment);
            mCardView = (CardView) itemView.findViewById(R.id.card_view);
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

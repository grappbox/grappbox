package com.grappbox.grappbox.adapter;

import android.accounts.AccountManager;
import android.app.Activity;
import android.content.DialogInterface;
import android.content.Intent;
import android.database.Cursor;
import android.os.AsyncTask;
import android.support.v4.content.ContextCompat;
import android.support.v7.app.AlertDialog;
import android.support.v7.widget.CardView;
import android.support.v7.widget.LinearLayoutManager;
import android.support.v7.widget.RecyclerView;
import android.view.Gravity;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.EditText;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.TextView;

import com.grappbox.grappbox.R;
import com.grappbox.grappbox.data.GrappboxContract;
import com.grappbox.grappbox.model.TimelineMessageCommentModel;
import com.grappbox.grappbox.model.TimelineModel;
import com.grappbox.grappbox.receiver.RefreshReceiver;
import com.grappbox.grappbox.singleton.Session;
import com.grappbox.grappbox.sync.GrappboxJustInTimeService;
import com.grappbox.grappbox.timeline_fragment.TimelineMessageCommentFragment;

import java.text.ParseException;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Arrays;
import java.util.Collection;
import java.util.Collections;
import java.util.Comparator;
import java.util.List;

public class TimelineMessageCommentAdapter extends RecyclerView.Adapter<RecyclerView.ViewHolder> {

    private static final String LOG_TAG = TimelineMessageCommentAdapter.class.getSimpleName();

    public static final int LOAD = 0;
    public static final int ACTION_ADD = 1;
    public static final int ACTION_EDIT = 2;
    public static final int ACTION_DELETE = 3;

    private static final int    TYPE_MESSAGE_COMMENTS = 0;

    private static final int    TIMELINE_ACTION_EDIT_MESSAGE = 0;
    private static final int    TIMELINE_ACTION_DELETE_MESSAGE = 1;


    private List<TimelineMessageCommentModel>   mComments;
    private TimelineModel   mParent;
    private RefreshReceiver mRefreshReceiver;
    private LayoutInflater  mInflater;
    private Activity        mContext;
    private LinearLayoutManager mLinearLayoutManager;
    private int _currentAction;

    public TimelineMessageCommentAdapter(Activity context, LinearLayoutManager linearLayoutManager) {
        mContext = context;
        mInflater = LayoutInflater.from(context);
        mComments = new ArrayList<>();
        mLinearLayoutManager = linearLayoutManager;
        _currentAction = 0;
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

    public void mergeItem(Collection<TimelineMessageCommentModel> items){
        MergeDataItem merge = new MergeDataItem();
        merge.execute(items);
    }

    private RecyclerView.ViewHolder createCommentViewHolder(ViewGroup parent){
        RecyclerView.ViewHolder vh = new TimelineMessageCommentHolder(mInflater.inflate(R.layout.list_item_timeline_message_comment_list, parent, false), parent);
        return vh;
    }

    public RecyclerView.ViewHolder onCreateViewHolder(ViewGroup parent, int viewType) {
        switch (viewType){
            case TYPE_MESSAGE_COMMENTS:
                return createCommentViewHolder(parent);
            default:
                throw new IllegalArgumentException("Bad viewTYpe : " + viewType);
        }
    }

    @Override
    public int getItemViewType(int position) {
        return TYPE_MESSAGE_COMMENTS;
    }

    private void bindComment(TimelineMessageCommentHolder holder, final TimelineMessageCommentModel item, final int position){
        final AccountManager am = AccountManager.get(mContext);
        final long uid = Long.parseLong(am.getUserData(Session.getInstance(mContext).getCurrentAccount(), GrappboxJustInTimeService.EXTRA_USER_ID));
        Cursor cursorUserId = mContext.getContentResolver().query(GrappboxContract.UserEntry.CONTENT_URI,
                new String[] {GrappboxContract.UserEntry.TABLE_NAME + "." + GrappboxContract.UserEntry.COLUMN_GRAPPBOX_ID},
                GrappboxContract.UserEntry.TABLE_NAME + "." + GrappboxContract.UserEntry._ID + " =?",
                new String[]{String.valueOf(uid)},
                null);
        holder.mMessage.setText(item._comment);
        holder.mLastUpdate.setText(item._lastupdate);
        if (cursorUserId == null || !cursorUserId.moveToFirst())
            return ;
        if (Long.valueOf(item._createId) == cursorUserId.getLong(0)) {
            holder.mlayout.setGravity(Gravity.END);
            holder.mAvatar.setVisibility(View.GONE);
            holder.mCardView.setCardBackgroundColor(ContextCompat.getColor(mContext, R.color.GrappOrange));
            holder.mMessage.setTextColor(ContextCompat.getColor(mContext, R.color.GrappWhiteLight));
            holder.mLastUpdate.setTextColor(ContextCompat.getColor(mContext, R.color.GrappWhiteLight));
        } else {
            holder.mlayout.setGravity(Gravity.START);
            holder.mAvatar.setVisibility(View.VISIBLE);
            holder.mCardView.setCardBackgroundColor(ContextCompat.getColor(mContext, R.color.GrappWhiteLight));
            holder.mMessage.setTextColor(ContextCompat.getColor(mContext, R.color.GrappBlackDark));
            holder.mLastUpdate.setTextColor(ContextCompat.getColor(mContext, R.color.GrappBlackDark));
        }
        holder.mCardView.setOnLongClickListener(new View.OnLongClickListener() {
            @Override
            public boolean onLongClick(View v) {
                AlertDialog.Builder builder = new AlertDialog.Builder(mContext);
                List<String> items = new ArrayList<String>();
                Cursor cursorUserId = mContext.getContentResolver().query(GrappboxContract.UserEntry.CONTENT_URI,
                        new String[] {GrappboxContract.UserEntry.TABLE_NAME + "." + GrappboxContract.UserEntry.COLUMN_GRAPPBOX_ID},
                        GrappboxContract.UserEntry.TABLE_NAME + "." + GrappboxContract.UserEntry._ID + " =?",
                        new String[]{String.valueOf(uid)},
                        null);
                if (cursorUserId == null || !cursorUserId.moveToFirst() || Long.valueOf(item._createId) != cursorUserId.getLong(0))
                    return false;
                items.addAll(Arrays.asList(mContext.getResources().getStringArray(R.array.labels_timeline_actions_user)));
                String[] actions = new String[items.size()];
                actions = items.toArray(actions);
                builder.setItems(actions, new DialogInterface.OnClickListener() {
                    @Override
                    public void onClick(DialogInterface dialogInterface, int which) {
                        switch (which){

                            case TIMELINE_ACTION_EDIT_MESSAGE:
                                mComments.remove(position);
                                messageCommentEdit(item);
                                break;

                            case TIMELINE_ACTION_DELETE_MESSAGE:
                                mComments.remove(position);
                                messageCommentDelete(item);
                                break;

                            default:
                                throw new IllegalArgumentException("Type doesn't exist");
                        }
                    }
                });
                builder.show();
                cursorUserId.close();
                return true;
            }
        });
        cursorUserId.close();
    }

    private void messageCommentEdit(final TimelineMessageCommentModel item){
        AlertDialog.Builder builder = new AlertDialog.Builder(mContext);
        final View dialog = mInflater.inflate(R.layout.dialog_timeline_add_comment, null);
        AccountManager am = AccountManager.get(mContext);
        final String token = am.getUserData(Session.getInstance(mContext).getCurrentAccount(), GrappboxJustInTimeService.EXTRA_API_TOKEN);
        builder.setView(dialog);
        EditText message = (EditText) dialog.findViewById(R.id.input_content);
        message.setText(item._comment);
        builder.setTitle(R.string.edit_message);
        builder.setPositiveButton(R.string.positive_response, new DialogInterface.OnClickListener() {
            @Override
            public void onClick(DialogInterface dialog, int which) {
                AlertDialog alertDialog = (AlertDialog)dialog;
                EditText message = (EditText)alertDialog.findViewById(R.id.input_content);
                if (message == null) {
                    alertDialog.cancel();
                    return;
                }
                Cursor cursorTimelineId = mContext.getContentResolver().query(GrappboxContract.TimelineMessageEntry.CONTENT_URI,
                        new String[] {GrappboxContract.TimelineEntry.TABLE_NAME + "." + GrappboxContract.TimelineEntry._ID},
                        GrappboxContract.TimelineMessageEntry.TABLE_NAME + "." +  GrappboxContract.TimelineMessageEntry.COLUMN_GRAPPBOX_ID + "=?",
                        new String [] {item._parentId},
                        null);
                if (cursorTimelineId == null || !cursorTimelineId.moveToFirst()) {
                    alertDialog.cancel();
                    return;
                }
                _currentAction = ACTION_EDIT;
                Intent editComment = new Intent(mContext, GrappboxJustInTimeService.class);
                editComment.setAction(GrappboxJustInTimeService.ACTION_TIMELINE_EDIT_COMMENT);
                editComment.putExtra(GrappboxJustInTimeService.EXTRA_API_TOKEN, token);
                editComment.putExtra(GrappboxJustInTimeService.EXTRA_TIMELINE_ID, cursorTimelineId.getLong(0));
                editComment.putExtra(GrappboxJustInTimeService.EXTRA_TIMELINE_PARENT_ID, Long.valueOf(item._parentId));
                editComment.putExtra(GrappboxJustInTimeService.EXTRA_TIMELINE_MESSAGE_ID, Long.valueOf(item._grappboxId));
                editComment.putExtra(GrappboxJustInTimeService.EXTRA_TIMELINE_MESSAGE, message.getText().toString());
                editComment.putExtra(GrappboxJustInTimeService.EXTRA_RESPONSE_RECEIVER, mRefreshReceiver);
                mContext.startService(editComment);
                cursorTimelineId.close();
            }
        });
        builder.setNegativeButton(R.string.negative_response, new DialogInterface.OnClickListener() {
            @Override
            public void onClick(DialogInterface dialog, int which) {
                dialog.cancel();
            }
        });
        builder.show();
    }

    private void messageCommentDelete(final TimelineMessageCommentModel item){
        AlertDialog.Builder builder = new AlertDialog.Builder(mContext);
        AccountManager am = AccountManager.get(mContext);
        final String token = am.getUserData(Session.getInstance(mContext).getCurrentAccount(), GrappboxJustInTimeService.EXTRA_API_TOKEN);
        builder.setTitle(R.string.delete_message);
        builder.setMessage(R.string.delete_message_warning);
        builder.setPositiveButton(R.string.positive_response, new DialogInterface.OnClickListener() {
            @Override
            public void onClick(DialogInterface dialog, int which) {
                AlertDialog alertDialog = (AlertDialog)dialog;

                Cursor cursorTimelineId = mContext.getContentResolver().query(GrappboxContract.TimelineMessageEntry.CONTENT_URI,
                        new String[] {GrappboxContract.TimelineEntry.TABLE_NAME + "." + GrappboxContract.TimelineEntry._ID},
                        GrappboxContract.TimelineMessageEntry.TABLE_NAME + "." +  GrappboxContract.TimelineMessageEntry.COLUMN_GRAPPBOX_ID + "=?",
                        new String [] {item._parentId},
                        null);
                if (cursorTimelineId == null || !cursorTimelineId.moveToFirst()) {
                    alertDialog.cancel();
                    return;
                }
                _currentAction = ACTION_DELETE;
                Intent deleteComment = new Intent(mContext, GrappboxJustInTimeService.class);
                deleteComment.setAction(GrappboxJustInTimeService.ACTION_TIMELINE_DELETE_COMMENT);
                deleteComment.putExtra(GrappboxJustInTimeService.EXTRA_API_TOKEN, token);
                deleteComment.putExtra(GrappboxJustInTimeService.EXTRA_TIMELINE_ID, cursorTimelineId.getLong(0));
                deleteComment.putExtra(GrappboxJustInTimeService.EXTRA_TIMELINE_PARENT_ID, Long.valueOf(item._parentId));
                deleteComment.putExtra(GrappboxJustInTimeService.EXTRA_TIMELINE_MESSAGE_ID, Long.valueOf(item._grappboxId));
                deleteComment.putExtra(GrappboxJustInTimeService.EXTRA_RESPONSE_RECEIVER, mRefreshReceiver);
                mContext.startService(deleteComment);
                cursorTimelineId.close();
            }
        });
        builder.setNegativeButton(R.string.negative_response, new DialogInterface.OnClickListener() {
            @Override
            public void onClick(DialogInterface dialog, int which) {
                dialog.cancel();
            }
        });
        builder.show();
    }

    @Override
    public void onBindViewHolder(RecyclerView.ViewHolder holder, int position) {
        switch (getItemViewType(position)){
            case TYPE_MESSAGE_COMMENTS :
                bindComment((TimelineMessageCommentHolder) holder, mComments.get(position), position);
                break;
            /*case TYPE_MESSAGE_REPLY :
                bindReply((CommentReplyHolder) holder);
                break;*/
            default :
                throw new IllegalArgumentException("Bad viewType : " + getItemViewType(position));
        }
    }

    @Override
    public int getItemCount() {
        return  mComments.size();
    }

    public void clear(){
        mComments.clear();
    }

    public int getSize() {return mComments.size();}

    public void setACtion(int action) {
        _currentAction = action;
    }

    private static class TimelineMessageCommentHolder extends RecyclerView.ViewHolder {

        ImageView       mAvatar;
        TextView        mMessage;
        TextView        mLastUpdate;
        ViewGroup       mparent;
        CardView        mCardView;
        LinearLayout    mlayout;


        public TimelineMessageCommentHolder(View itemView, ViewGroup root){
            super(itemView);
            mAvatar = (ImageView) itemView.findViewById(R.id.avatar);
            mMessage = (TextView) itemView.findViewById(R.id.messagecontent);
            mLastUpdate = (TextView) itemView.findViewById(R.id.messagelastupdate);
            mlayout = (LinearLayout) itemView.findViewById(R.id.layoutcomment);
            mCardView = (CardView) itemView.findViewById(R.id.card_view);
            mparent = root;

        }
    }

    private class MergeDataItem extends AsyncTask<Collection<TimelineMessageCommentModel>, Void, Collection<TimelineMessageCommentModel>> {

        @Override
        protected void onPostExecute(Collection<TimelineMessageCommentModel> timelineModels) {
            super.onPostExecute(timelineModels);
            mComments.clear();
            notifyDataSetChanged();
            mComments.addAll(timelineModels);
            notifyDataSetChanged();
            if (_currentAction == LOAD || _currentAction == ACTION_ADD || _currentAction == ACTION_EDIT) {
                mLinearLayoutManager.scrollToPosition(0);
                _currentAction = -1;
            }
        }

        @Override
        protected Collection<TimelineMessageCommentModel> doInBackground(Collection<TimelineMessageCommentModel>... params) {
            boolean exist;
            if (params == null || params.length < 1)
                throw new IllegalArgumentException();

            ArrayList<TimelineMessageCommentModel> timeline = new ArrayList<>();
            Collection<TimelineMessageCommentModel> newItems = params[0];

            for (TimelineMessageCommentModel item : newItems) {
                timeline.add(item);
            }
            for (TimelineMessageCommentModel model : mComments) {
                exist = false;
                for (TimelineMessageCommentModel item : newItems) {
                    if (item._id.equals(model._id))
                        exist = true;
                }
                if (!exist)
                    timeline.add(model);
            }
            Collections.sort(timeline, new StringDateComparator());
            return timeline;
        }

        class StringDateComparator implements Comparator<TimelineMessageCommentModel>
        {

            SimpleDateFormat dateFormat = new SimpleDateFormat("yyyy-MM-dd hh:mm:ss");

            @Override
            public int compare(TimelineMessageCommentModel o1, TimelineMessageCommentModel o2) {

                try {
                    return dateFormat.parse(o2._lastupdate).compareTo(dateFormat.parse(o1._lastupdate));
                } catch (ParseException e) {
                    e.printStackTrace();
                }
                return -1;
            }
        }
    }

}

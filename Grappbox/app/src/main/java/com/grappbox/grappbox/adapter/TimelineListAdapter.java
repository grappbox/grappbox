package com.grappbox.grappbox.adapter;

import android.accounts.AccountManager;
import android.app.Activity;
import android.content.ContentValues;
import android.content.Context;
import android.content.DialogInterface;
import android.content.Intent;
import android.database.Cursor;
import android.database.DataSetObserver;
import android.database.DatabaseUtils;
import android.database.MatrixCursor;
import android.database.MergeCursor;
import android.media.Image;
import android.os.AsyncTask;
import android.os.Parcelable;
import android.support.v4.content.ContextCompat;
import android.support.v4.view.animation.LinearOutSlowInInterpolator;
import android.support.v4.widget.CursorAdapter;
import android.support.v7.app.AlertDialog;
import android.support.v7.widget.CardView;
import android.support.v7.widget.LinearLayoutManager;
import android.support.v7.widget.RecyclerView;
import android.transition.TransitionManager;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.EditText;
import android.widget.ImageButton;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.TextView;
import android.widget.Toast;

import com.grappbox.grappbox.ProjectActivity;
import com.grappbox.grappbox.R;
import com.grappbox.grappbox.bugtracker_fragments.NewBugActivity;
import com.grappbox.grappbox.data.GrappboxContract;
import com.grappbox.grappbox.model.TimelineMessageCommentModel;
import com.grappbox.grappbox.model.TimelineModel;
import com.grappbox.grappbox.receiver.RefreshReceiver;
import com.grappbox.grappbox.singleton.Session;
import com.grappbox.grappbox.sync.GrappboxJustInTimeService;
import com.grappbox.grappbox.timeline_fragment.TimelineMessageCommentActivity;
import com.grappbox.grappbox.timeline_fragment.TimelineMessageCommentFragment;

import java.text.ParseException;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Arrays;
import java.util.Collection;
import java.util.Collections;
import java.util.Comparator;
import java.util.List;
import java.util.MissingResourceException;
import java.util.Objects;

/**
 * Created by tan_f on 28/09/2016.
 */

public class TimelineListAdapter extends RecyclerView.Adapter<RecyclerView.ViewHolder> {

    public static final int TYPE_TIMELINE_ENTRY = 0;

    private static final int TIMELINE_ACTION_VIEW_COMMENT = 0;
    private static final int TIMELINE_ACTION_ADD_TO_BUGTRACKER = 1;
    private static final int TIMELINE_ACTION_EDIT_MESSAGE = 2;
    private static final int TIMELINE_ACTION_DELETE_MESSAGE = 3;

    private Activity mContext;
    private List<TimelineModel> mDataset;
    private LayoutInflater  inflater;
    private RefreshReceiver mRefreshReceiver = null;
    private RecyclerView    mRecyclerView;
    private int             mExpandedPosition = -1;

    public TimelineListAdapter(Activity context, RecyclerView rv)
    {
        super();
        mContext = context;
        mDataset = new ArrayList<>(0);
        inflater = LayoutInflater.from(context);
        mRecyclerView = rv;
        mExpandedPosition = -1;
    }

    private RecyclerView.ViewHolder createTimelineEntryHolder(ViewGroup parent){
        final TimelineHolder holder = new TimelineHolder(inflater.inflate(R.layout.list_item_timeline_list, parent, false), parent);
        return holder;
    }

    public void setRefreshReciver(RefreshReceiver refreshReciver)
    {
        mRefreshReceiver = refreshReciver;
    }

    public void add(TimelineModel item){
        mDataset.add(item);
        notifyDataSetChanged();
    }

    public void add(Collection<? extends TimelineModel> items){
        mDataset.addAll(items);
        notifyDataSetChanged();
    }

    public void mergeItem(Collection<TimelineModel> items){
        MergeDataItem merge = new MergeDataItem();
        merge.execute(items);
    }

    @Override
    public RecyclerView.ViewHolder onCreateViewHolder(ViewGroup parent, int viewType) {
        switch (viewType){
            case TYPE_TIMELINE_ENTRY:
                return createTimelineEntryHolder(parent);
        }
        return null;
    }

    private void messageDelete(final TimelineModel item)
    {
        AlertDialog.Builder builder = new AlertDialog.Builder(mContext);AccountManager am = AccountManager.get(mContext);
        final String token = am.getUserData(Session.getInstance(mContext).getCurrentAccount(), GrappboxJustInTimeService.EXTRA_API_TOKEN);
        builder.setTitle(R.string.delete_message);
        builder.setMessage(R.string.delete_message_warning);
        builder.setPositiveButton(R.string.positive_response, new DialogInterface.OnClickListener() {
            @Override
            public void onClick(DialogInterface dialog, int which) {
                Cursor cursorTimelineId = mContext.getContentResolver().query(GrappboxContract.TimelineMessageEntry.CONTENT_URI,
                        new String[] {GrappboxContract.TimelineEntry.TABLE_NAME + "." + GrappboxContract.TimelineEntry._ID},
                        GrappboxContract.TimelineMessageEntry.TABLE_NAME + "." +  GrappboxContract.TimelineMessageEntry.COLUMN_GRAPPBOX_ID + "=?",
                        new String[]{String.valueOf(item._grappboxId)},
                        null);
                if (cursorTimelineId == null || !cursorTimelineId.moveToFirst())
                    return;
                Intent deleteMessage = new Intent(mContext, GrappboxJustInTimeService.class);
                deleteMessage.setAction(GrappboxJustInTimeService.ACTION_TIMELINE_DELETE_MESSAGE);
                deleteMessage.putExtra(GrappboxJustInTimeService.EXTRA_API_TOKEN, token);
                deleteMessage.putExtra(GrappboxJustInTimeService.EXTRA_TIMELINE_ID, cursorTimelineId.getLong(0));
                deleteMessage.putExtra(GrappboxJustInTimeService.EXTRA_TIMELINE_MESSAGE_ID, Long.valueOf(item._grappboxId));
                deleteMessage.putExtra(GrappboxJustInTimeService.EXTRA_RESPONSE_RECEIVER, mRefreshReceiver);
                mContext.startService(deleteMessage);
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

    private void messageEdit(final TimelineModel item)
    {
        AlertDialog.Builder builder = new AlertDialog.Builder(mContext);
        final View dialog = inflater.inflate(R.layout.dialog_timeline_add_message, null);
        AccountManager am = AccountManager.get(mContext);
        final String token = am.getUserData(Session.getInstance(mContext).getCurrentAccount(), GrappboxJustInTimeService.EXTRA_API_TOKEN);
        builder.setView(dialog);
        EditText title = (EditText) dialog.findViewById(R.id.input_title);
        EditText message = (EditText) dialog.findViewById(R.id.input_content);
        title.setText(item._title);
        message.setText(item._message);
        builder.setTitle(R.string.edit_message);
        builder.setPositiveButton(R.string.positive_response, new DialogInterface.OnClickListener() {
            @Override
            public void onClick(DialogInterface dialog, int which) {
                AlertDialog alertDialog = (AlertDialog) dialog;
                EditText title = (EditText) alertDialog.findViewById(R.id.input_title);
                EditText message = (EditText) alertDialog.findViewById(R.id.input_content);

                if (title == null || message == null) {
                    alertDialog.cancel();
                    return;
                }
                Cursor cursorTimelineId = mContext.getContentResolver().query(GrappboxContract.TimelineMessageEntry.CONTENT_URI,
                        new String[] {GrappboxContract.TimelineEntry.TABLE_NAME + "." + GrappboxContract.TimelineEntry._ID},
                        GrappboxContract.TimelineMessageEntry.TABLE_NAME + "." +  GrappboxContract.TimelineMessageEntry.COLUMN_GRAPPBOX_ID + "=?",
                        new String[] {String.valueOf(item._grappboxId)},
                        null);
                if (cursorTimelineId == null || !cursorTimelineId.moveToFirst()) {
                    alertDialog.cancel();
                    return;
                }
                mRecyclerView.getLayoutManager().scrollToPosition(0);
                Intent editMessage = new Intent(mContext, GrappboxJustInTimeService.class);
                editMessage.setAction(GrappboxJustInTimeService.ACTION_TIMELINE_EDIT_MESSAGE);
                editMessage.putExtra(GrappboxJustInTimeService.EXTRA_API_TOKEN, token);
                editMessage.putExtra(GrappboxJustInTimeService.EXTRA_TIMELINE_ID, cursorTimelineId.getLong(0));
                editMessage.putExtra(GrappboxJustInTimeService.EXTRA_TIMELINE_MESSAGE_ID, Long.valueOf(item._grappboxId));
                editMessage.putExtra(GrappboxJustInTimeService.EXTRA_TIMELINE_TITLE, title.getText().toString());
                editMessage.putExtra(GrappboxJustInTimeService.EXTRA_TIMELINE_MESSAGE, message.getText().toString());
                editMessage.putExtra(GrappboxJustInTimeService.EXTRA_OFFSET, 0);
                editMessage.putExtra(GrappboxJustInTimeService.EXTRA_OFFSET, mDataset.size());
                editMessage.putExtra(GrappboxJustInTimeService.EXTRA_RESPONSE_RECEIVER, mRefreshReceiver);
                mContext.startService(editMessage);
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

    private void launchMessageComment(final TimelineModel item)
    {
        Intent launchMessageComment = new Intent(mContext, TimelineMessageCommentActivity.class);

        launchMessageComment.putExtra(TimelineMessageCommentActivity.EXTRA_TIMELINE_MODEL, item);
        launchMessageComment.putExtra(ProjectActivity.EXTRA_PROJECT_ID, mContext.getIntent().getLongExtra(ProjectActivity.EXTRA_PROJECT_ID, -1));
        launchMessageComment.putExtra(TimelineMessageCommentActivity.EXTRA_PARENT_ID, item._grappboxId);
        mContext.startActivity(launchMessageComment);
    }

    private void launchBugtrackerActivity(final TimelineModel item){
        Intent toBugtracker = new Intent(mContext, NewBugActivity.class);
        toBugtracker.setAction(NewBugActivity.ACTION_NEW_FROM_TIMELINE);
        toBugtracker.putExtra(NewBugActivity.EXTRA_TITLE_BUG, item._title);
        toBugtracker.putExtra(NewBugActivity.EXTRA_MESSAGE_BUG, item._message);
        mContext.startActivity(toBugtracker);
    }

    private void bindTimelineEntry(final TimelineModel item, final TimelineHolder holder, final int position){
        final boolean isExpanded = position == mExpandedPosition;
        final AccountManager am = AccountManager.get(mContext);
        final long uid = Long.parseLong(am.getUserData(Session.getInstance(mContext).getCurrentAccount(), GrappboxJustInTimeService.EXTRA_USER_ID));
        Cursor cursorUserId = mContext.getContentResolver().query(GrappboxContract.UserEntry.CONTENT_URI,
                new String[] {GrappboxContract.UserEntry.TABLE_NAME + "." + GrappboxContract.UserEntry.COLUMN_GRAPPBOX_ID},
                GrappboxContract.UserEntry.TABLE_NAME + "." + GrappboxContract.UserEntry._ID + " =?",
                new String[]{String.valueOf(uid)},
                null);
        if (cursorUserId == null || !cursorUserId.moveToFirst())
            return;

        holder.mTitle.setText(item._title);
        holder.mMessage.setText(item._message);
        holder.mLastUpdate.setText(item._lastUpadte);
        holder.mActionLayout.setVisibility(isExpanded ? View.VISIBLE : View.GONE);
        holder.mListView.setActivated(isExpanded);
        holder.mListView.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                mExpandedPosition = isExpanded ? -1 : position;
//                TransitionManager.beginDelayedTransition(mRecyclerView);
                notifyDataSetChanged();
            }
        });
        holder.mToComment.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                launchMessageComment(item);
            }
        });
        if (Long.valueOf(item._createID) == cursorUserId.getLong(0)) {
            holder.mEdit.setVisibility(View.VISIBLE);
            holder.mDelete.setVisibility(View.VISIBLE);
            holder.mEdit.setOnClickListener(new View.OnClickListener() {
                @Override
                public void onClick(View v) {
                    mDataset.remove(position);
                    messageEdit(item);
                }
            });
            holder.mDelete.setOnClickListener(new View.OnClickListener() {
                @Override
                public void onClick(View v) {
                    mDataset.remove(position);
                    messageDelete(item);
                }
            });
        } else {
            holder.mEdit.setVisibility(View.GONE);
            holder.mDelete.setVisibility(View.GONE);
        }
        holder.mToBugtracker.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                launchBugtrackerActivity(item);
            }
        });
        holder.mListView.setOnLongClickListener(new View.OnLongClickListener() {
            @Override
            public boolean onLongClick(View v) {
                AlertDialog.Builder builder = new AlertDialog.Builder(mContext);
                List<String> items = new ArrayList<String>();
                items.addAll(Arrays.asList(mContext.getResources().getStringArray(R.array.labels_timeline_actions)));
                Cursor cursorUserId = mContext.getContentResolver().query(GrappboxContract.UserEntry.CONTENT_URI,
                        new String[] {GrappboxContract.UserEntry.TABLE_NAME + "." + GrappboxContract.UserEntry.COLUMN_GRAPPBOX_ID},
                        GrappboxContract.UserEntry.TABLE_NAME + "." + GrappboxContract.UserEntry._ID + " =?",
                        new String[]{String.valueOf(uid)},
                        null);
                if (cursorUserId == null || !cursorUserId.moveToFirst())
                    return false;
                if (Long.valueOf(item._createID) == cursorUserId.getLong(0))
                    items.addAll(Arrays.asList(mContext.getResources().getStringArray(R.array.labels_timeline_actions_user)));
                String[] actions = new String[items.size()];
                actions = items.toArray(actions);
                builder.setItems(actions, new DialogInterface.OnClickListener() {
                    @Override
                    public void onClick(DialogInterface dialogInterface, int which) {
                        switch (which){
                            case TIMELINE_ACTION_VIEW_COMMENT:
                                launchMessageComment(item);
                                break;

                            case TIMELINE_ACTION_ADD_TO_BUGTRACKER:
                                launchBugtrackerActivity(item);
                                break;

                            case TIMELINE_ACTION_EDIT_MESSAGE:
                                mDataset.remove(position);
                                messageEdit(item);
                                break;

                            case TIMELINE_ACTION_DELETE_MESSAGE:
                                mDataset.remove(position);
                                messageDelete(item);
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
        holder.mAnswer.setText(String.valueOf(item._countAnswer));
        cursorUserId.close();
    }

    @Override
    public void onBindViewHolder(RecyclerView.ViewHolder holder, int position) {
        switch (getItemViewType(position)){
            case TYPE_TIMELINE_ENTRY:
                bindTimelineEntry(getItemAt(position), (TimelineHolder) holder, position);
                break;
        }
    }

    public TimelineModel getItemAt(int position){
        return mDataset.get(position);
    }

    @Override
    public void onBindViewHolder(RecyclerView.ViewHolder holder, int position, List<Object> payloads) {
        super.onBindViewHolder(holder, position, payloads);
    }

    @Override
    public int getItemViewType(int position) {
        return TYPE_TIMELINE_ENTRY;
    }

    @Override
    public int getItemCount() {
        return mDataset.size();
    }

    public boolean isEmpty(){
        return mDataset.isEmpty();
    }

    @Override
    public void setHasStableIds(boolean hasStableIds) {
        super.setHasStableIds(true);
    }

    private static class TimelineHolder extends RecyclerView.ViewHolder {

        ImageView   mAvatar;
        TextView    mTitle;
        TextView    mMessage;
        TextView    mLastUpdate;
        TextView    mAnswer;
        ViewGroup   mparent;
        LinearLayout mListView;
        LinearLayout mActionLayout;
        ImageButton mToComment;
        ImageButton mToBugtracker;
        ImageButton mEdit;
        ImageButton mDelete;

        public TimelineHolder(View itemView, ViewGroup root){
            super(itemView);
            mAvatar = (ImageView) itemView.findViewById(R.id.avatar);
            mTitle = (TextView) itemView.findViewById(R.id.title);
            mMessage = (TextView) itemView.findViewById(R.id.messagecontent);
            mLastUpdate = (TextView) itemView.findViewById(R.id.messagelastupdate);
            mAnswer = (TextView) itemView.findViewById(R.id.answer);
            mListView = (LinearLayout) itemView.findViewById(R.id.card_view);
            mActionLayout = (LinearLayout) itemView.findViewById(R.id.message_action);
            mToComment = (ImageButton) itemView.findViewById(R.id.to_comment);
            mToBugtracker = (ImageButton) itemView.findViewById(R.id.add_to_bucktracker) ;
            mEdit = (ImageButton) itemView.findViewById(R.id.edit_message);
            mDelete = (ImageButton) itemView.findViewById(R.id.delete_message);
            mparent = root;

        }
    }

    private class MergeDataItem extends AsyncTask<Collection<TimelineModel>, Void, Collection<TimelineModel>> {

        @Override
        protected void onPostExecute(Collection<TimelineModel> timelineModels) {
            super.onPostExecute(timelineModels);
            mDataset.clear();
            notifyDataSetChanged();
            mDataset.addAll(timelineModels);
            notifyDataSetChanged();
        }

        @Override
        protected Collection<TimelineModel> doInBackground(Collection<TimelineModel>... params) {
            boolean exist;
            if (params == null || params.length < 1)
                throw new IllegalArgumentException();

            ArrayList<TimelineModel> timeline = new ArrayList<>();
            Collection<TimelineModel> newItems = params[0];

            for (TimelineModel item : newItems) {
                timeline.add(item);
            }
            for (TimelineModel model : mDataset) {
                exist = false;
                for (TimelineModel item : newItems) {
                    if (item._grappboxId.equals(model._grappboxId))
                        exist = true;
                }
                if (!exist)
                    timeline.add(model);
            }
            Collections.sort(timeline, new StringDateComparator());
            return timeline;
        }

        class StringDateComparator implements Comparator<TimelineModel>
        {

            SimpleDateFormat dateFormat = new SimpleDateFormat("yyyy-MM-dd hh:mm:ss");

            @Override
            public int compare(TimelineModel o1, TimelineModel o2) {

                try {
                    return dateFormat.parse(o2._lastUpadte).compareTo(dateFormat.parse(o1._lastUpadte));
                } catch (ParseException e) {
                    e.printStackTrace();
                }
                return -1;
            }
        }
    }

}

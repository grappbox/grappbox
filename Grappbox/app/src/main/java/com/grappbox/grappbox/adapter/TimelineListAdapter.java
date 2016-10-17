package com.grappbox.grappbox.adapter;

import android.accounts.AccountManager;
import android.app.Activity;
import android.content.DialogInterface;
import android.content.Intent;
import android.database.Cursor;
import android.database.MergeCursor;
import android.support.v7.app.AlertDialog;
import android.support.v7.widget.RecyclerView;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.EditText;
import android.widget.ImageButton;
import android.widget.ImageView;
import android.widget.TextView;

import com.grappbox.grappbox.ProjectActivity;
import com.grappbox.grappbox.R;
import com.grappbox.grappbox.data.GrappboxContract;
import com.grappbox.grappbox.model.TimelineModel;
import com.grappbox.grappbox.receiver.RefreshReceiver;
import com.grappbox.grappbox.singleton.Session;
import com.grappbox.grappbox.sync.GrappboxJustInTimeService;
import com.grappbox.grappbox.timeline_fragment.TimelineMessageCommentActivity;

import java.util.ArrayList;
import java.util.Collection;
import java.util.List;

/**
 * Created by tan_f on 28/09/2016.
 */

public class TimelineListAdapter extends RecyclerView.Adapter<RecyclerView.ViewHolder> {

    private Activity mContext;
    private List<TimelineModel> mDataset;
    private LayoutInflater  inflater;
    private Cursor          mCursor = null;
    private RefreshReceiver mRefreshReceiver = null;

    public static final int TYPE_TIMELINE_ENTRY = 0;

    public TimelineListAdapter(Activity context)
    {
        super();
        mContext = context;
        mDataset = new ArrayList<>(0);
        inflater = LayoutInflater.from(context);
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
        for (TimelineModel item : items){
            mDataset.add(item);
        }
//        mDataset.addAll(items);
        notifyDataSetChanged();
    }

    public Cursor getCursor()
    {
        return mCursor;
    }

    public void setCursor(Cursor data){
        mCursor = data;
    }

    @Override
    public RecyclerView.ViewHolder onCreateViewHolder(ViewGroup parent, int viewType) {
        switch (viewType){
            case TYPE_TIMELINE_ENTRY:
                return createTimelineEntryHolder(parent);
        }
        return null;
    }

    public void bindTimelineEntry(final TimelineModel item, TimelineHolder holder){
        holder.mTitle.setText(item._title);
        holder.mMessage.setText(item._message);
        holder.mLastUpdate.setText(item._lastUpadte);
        holder.mAnswer.setText(String.valueOf(item._countAnswer));
        holder.mAnswer.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                Intent launchMessageComment = new Intent(mContext, TimelineMessageCommentActivity.class);
                launchMessageComment.putExtra(TimelineMessageCommentActivity.EXTRA_TIMELINE_MODEL, item);
                TimelineModel model = launchMessageComment.getParcelableExtra(TimelineMessageCommentActivity.EXTRA_TIMELINE_MODEL);
                mContext.startActivity(launchMessageComment);
            }
        });
        holder.mEdit.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
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
                                new String[] {GrappboxContract.TimelineEntry.TABLE_NAME + "." + GrappboxContract.TimelineEntry.COLUMN_GRAPPBOX_ID,
                                        GrappboxContract.TimelineMessageEntry.TABLE_NAME + "." + GrappboxContract.TimelineMessageEntry.COLUMN_GRAPPBOX_ID},
                                GrappboxContract.TimelineEntry.TABLE_NAME + "." +  GrappboxContract.TimelineEntry.COLUMN_LOCAL_PROJECT_ID + "=? AND " + GrappboxContract.TimelineEntry.TABLE_NAME + "." +  GrappboxContract.TimelineEntry.COLUMN_TYPE_ID + " =?",
                                new String[]{String.valueOf(mContext.getIntent().getLongExtra(ProjectActivity.EXTRA_PROJECT_ID, -1)), String.valueOf(item._timelineType)},
                                null);
                        if (cursorTimelineId == null || !cursorTimelineId.moveToFirst())
                            return;
                        Intent editMessage = new Intent(mContext, GrappboxJustInTimeService.class);
                        editMessage.setAction(GrappboxJustInTimeService.ACTION_TIMELINE_EDIT_MESSAGE);
                        editMessage.putExtra(GrappboxJustInTimeService.EXTRA_API_TOKEN, token);
                        editMessage.putExtra(GrappboxJustInTimeService.EXTRA_TIMELINE_ID, cursorTimelineId.getLong(0));
                        editMessage.putExtra(GrappboxJustInTimeService.EXTRA_TIMELINE_MESSAGE_ID, cursorTimelineId.getLong(1));
                        editMessage.putExtra(GrappboxJustInTimeService.EXTRA_TIMELINE_TITLE, title.getText().toString());
                        editMessage.putExtra(GrappboxJustInTimeService.EXTRA_TIMELINE_MESSAGE, message.getText().toString());
                        editMessage.putExtra(GrappboxJustInTimeService.EXTRA_RESPONSE_RECEIVER, mRefreshReceiver);
                        mContext.startService(editMessage);
                        cursorTimelineId.close();
                    }
                });
                builder.setNegativeButton(R.string.negative_response, new DialogInterface.OnClickListener() {
                    @Override
                    public void onClick(DialogInterface dialog, int which) {
                        dialog.cancel();
                        return;
                    }
                });
                builder.show();
            }
        });
    }

    @Override
    public void onBindViewHolder(RecyclerView.ViewHolder holder, int position) {
        switch (getItemViewType(position)){
            case TYPE_TIMELINE_ENTRY:
                bindTimelineEntry(getItemAt(position), (TimelineHolder) holder);
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

    public void clear(){
        mDataset.clear();
        notifyDataSetChanged();
    }

    private static class TimelineHolder extends RecyclerView.ViewHolder {

        ImageView   mAvatar;
        TextView    mTitle;
        TextView    mMessage;
        TextView    mLastUpdate;
        TextView    mAnswer;
        ImageButton mEdit;
        ImageButton mDelete;
        ViewGroup   mparent;

        public TimelineHolder(View itemView, ViewGroup root){
            super(itemView);
            mAvatar = (ImageView) itemView.findViewById(R.id.avatar);
            mTitle = (TextView) itemView.findViewById(R.id.title);
            mMessage = (TextView) itemView.findViewById(R.id.messagecontent);
            mLastUpdate = (TextView) itemView.findViewById(R.id.messagelastupdate);
            mAnswer = (TextView) itemView.findViewById(R.id.answer);
            mEdit = (ImageButton) itemView.findViewById(R.id.edit);
            /*mDelete = (ImageButton) itemView.findViewById(R.id.delete);*/
            mparent = root;

        }
    }
}

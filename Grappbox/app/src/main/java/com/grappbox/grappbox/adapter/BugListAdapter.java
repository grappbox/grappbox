package com.grappbox.grappbox.adapter;

import android.content.Context;
import android.database.Cursor;
import android.os.Trace;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.CursorAdapter;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.TextView;

import com.grappbox.grappbox.R;
import com.grappbox.grappbox.Utils;
import com.grappbox.grappbox.data.GrappboxContract;
import com.grappbox.grappbox.data.GrappboxContract.BugAssignationEntry;
import com.grappbox.grappbox.data.GrappboxContract.BugEntry;
import com.grappbox.grappbox.data.GrappboxContract.TagEntry;

import java.text.DateFormat;
import java.text.ParseException;
import java.util.ArrayList;
import java.util.Date;

/**
 * Created by marcw on 17/09/2016.
 */
public class BugListAdapter extends CursorAdapter {

    public static final String[] projection = new String[]{
            BugEntry.TABLE_NAME + "." + BugEntry._ID + " AS _id",
            BugEntry.TABLE_NAME + "." + BugEntry.COLUMN_TITLE,
            BugEntry.TABLE_NAME + "." + BugEntry.COLUMN_DATE_DELETED_UTC,
            BugEntry.TABLE_NAME + "." + BugEntry.COLUMN_DATE_LAST_EDITED_UTC,
            BugEntry.TABLE_NAME + "." + BugEntry.COLUMN_LOCAL_PROJECT_ID
    };

    public static final int COLUMN_BUG_ID = 0;
    public static final int COLUMN_TITLE = 1;
    public static final int COLUMN_DELETED_UTC = 2;
    public static final int COLUMN_LAST_EDIT_UTC = 3;
    private static final String LOG_TAG = BugListAdapter.class.getSimpleName();

    private Context mContext;
    private ArrayList<Cursor> tags;
    private ArrayList<Long> nbAssignee;
    private ArrayList<Long> nbComments;

    public class BugEntryViewHolder{
        ImageView mAvatar;
        TextView mTitle, mDateStatus, mNbAssignee, mNbComments;
        LinearLayout mTagContainer;

        public BugEntryViewHolder(View v){
            mAvatar = (ImageView) v.findViewById(R.id.avatar);
            mTitle = (TextView) v.findViewById(R.id.title);
            mDateStatus = (TextView) v.findViewById(R.id.date_status);
            mNbAssignee = (TextView) v.findViewById(R.id.nb_assignees);
            mNbComments = (TextView) v.findViewById(R.id.nb_comments);
            mTagContainer = (LinearLayout) v.findViewById(R.id.tag_container);
        }
    }

    public BugListAdapter(Context context, Cursor c, int flags) {
        super(context, c, flags);
        mContext = context;
        nbAssignee = new ArrayList<>();
        nbComments = new ArrayList<>();
        tags = new ArrayList<>();
    }

    @Override
    public View newView(Context context, Cursor cursor, ViewGroup viewGroup) {
        Trace.beginSection("Create list view");
        View v = LayoutInflater.from(context).inflate(R.layout.list_item_bugtracker_list, viewGroup, false);

        BugEntryViewHolder vh = new BugEntryViewHolder(v);
        v.setTag(vh);
        Trace.endSection();
        return v;
    }

    @Override
    public void bindView(View view, Context context, Cursor cursor) {
        Trace.beginSection("Bind view");
        BugEntryViewHolder vh = (BugEntryViewHolder) view.getTag();
        boolean isClosed = !cursor.isNull(COLUMN_DELETED_UTC);

        vh.mTitle.setText(cursor.getString(COLUMN_TITLE));
        Date date = null;
        try {
            date = Utils.Date.convertUTCToPhone(cursor.getString(isClosed ? COLUMN_DELETED_UTC : COLUMN_LAST_EDIT_UTC));
            /*vh.mNbAssignee.setText(String.valueOf(nbAssignee.get(cursor.getPosition())));
            vh.mNbComments.setText(String.valueOf(nbComments.get(cursor.getPosition())));
            if (tags.get(cursor.getPosition()) != null && tags.get(cursor.getPosition()).moveToFirst()){
                TextView newTag = (TextView) LayoutInflater.from(mContext).inflate(R.layout.list_item_bugtracker_tagitem, vh.mTagContainer);
                newTag.setText(tags.get(cursor.getPosition()).getString(0));
            }*/
            vh.mDateStatus.setText(mContext.getString(R.string.bug_status_date, mContext.getString(isClosed ? R.string.bug_status_closed : R.string.bug_status_opened), DateFormat.getDateInstance().format(date)));
        } catch (ParseException e) {
            e.printStackTrace();
        } finally {
            Trace.endSection();
        }

    }



    @Override
    public Cursor swapCursor(Cursor newCursor) {
        Trace.beginSection("Swap Cursor");
        tags.clear();
        nbComments.clear();
        nbAssignee.clear();
        if (newCursor == null|| !newCursor.moveToFirst())
            return super.swapCursor(null);
        do {
            Trace.beginSection("Load additionnal cursors");
            /*Cursor nbAssigneeCursor = mContext.getContentResolver().query(BugAssignationEntry.CONTENT_URI, new String[]{"COUNT(" + BugAssignationEntry._ID + ") AS count_assignee"},
                    BugAssignationEntry.COLUMN_LOCAL_BUG_ID + "=?", new String[]{String.valueOf(newCursor.getLong(COLUMN_BUG_ID))}, null);
            Cursor nbCommentsCursor = mContext.getContentResolver().query(BugEntry.CONTENT_URI, new String[]{"COUNT(" + BugEntry._ID + ") AS count_assignee"},
                    BugEntry.COLUMN_LOCAL_PARENT_ID + "=?", new String[]{String.valueOf(newCursor.getLong(COLUMN_BUG_ID))}, null);
            Cursor tagsCursor = mContext.getContentResolver().query(BugEntry.buildBugWithTag(), new String[]{TagEntry.TABLE_NAME + "." + TagEntry.COLUMN_NAME},
                    GrappboxContract.BugTagEntry.TABLE_NAME + "." + GrappboxContract.BugTagEntry.COLUMN_LOCAL_BUG_ID + "=?", new String[]{String.valueOf(newCursor.getLong(COLUMN_BUG_ID))}, null);

            if (nbAssigneeCursor == null || nbCommentsCursor == null || !nbAssigneeCursor.moveToFirst() || !nbCommentsCursor.moveToFirst()){
                nbComments.add((long) 0);
                nbAssignee.add((long) 0);
            } else {
                nbAssignee.add(nbAssigneeCursor.getLong(0));
                nbComments.add(nbCommentsCursor.getLong(0));
                nbAssigneeCursor.close();
                nbCommentsCursor.close();
            }
            tags.add(tagsCursor);*/
            Trace.endSection();
        } while (newCursor.moveToNext());
        newCursor.moveToFirst();
        Trace.endSection();
        return super.swapCursor(newCursor);
    }
}

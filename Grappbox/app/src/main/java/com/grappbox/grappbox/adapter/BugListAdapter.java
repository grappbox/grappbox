package com.grappbox.grappbox.adapter;

import android.content.Context;
import android.database.Cursor;
import android.os.AsyncTask;
import android.os.Trace;
import android.support.annotation.NonNull;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.CursorAdapter;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.TextView;

import com.grappbox.grappbox.R;
import com.grappbox.grappbox.Utils;
import com.grappbox.grappbox.data.GrappboxContract.BugEntry;

import java.text.DateFormat;
import java.text.ParseException;
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

    public static class BugEntryViewHolder{
        ImageView mAvatar;
        TextView mTitle, mDateStatus, mNbAssignee, mNbComments;
        LinearLayout mTagContainer;

        public BugEntryViewHolder(View v){
            mAvatar = (ImageView) v.findViewById(R.id.avatar);
            mTitle = (TextView) v.findViewById(R.id.title);
            mDateStatus = (TextView) v.findViewById(R.id.date_status);
            mNbAssignee = (TextView) v.findViewById(R.id.nb_assignees);
            mNbComments = (TextView) v.findViewById(R.id.nb_comments);
            //mTagContainer = (LinearLayout) v.findViewById(R.id.tag_container);
        }
    }

    public BugListAdapter(Context context, Cursor c, int flags) {
        super(context, c, flags);
        mContext = context;
    }

    @Override
    public View newView(Context context, Cursor cursor, ViewGroup viewGroup) {

        View v = LayoutInflater.from(context).inflate(R.layout.list_item_bugtracker_list, viewGroup, false);

        BugEntryViewHolder vh = new BugEntryViewHolder(v);
        v.setTag(vh);

        return v;
    }

    @Override
    public void bindView(View view, Context context, Cursor cursor) {
        Trace.beginSection("Bind view #" + cursor.getPosition());
        BugEntryViewHolder vh = (BugEntryViewHolder) view.getTag();
        boolean isClosed = !cursor.isNull(COLUMN_DELETED_UTC);


        Date date = null;
        try {
            date = Utils.Date.convertUTCToPhone(cursor.getString(isClosed ? COLUMN_DELETED_UTC : COLUMN_LAST_EDIT_UTC));
            vh.mTitle.setText(cursor.getString(COLUMN_TITLE));
            vh.mDateStatus.setText(mContext.getString(R.string.bug_status_date, mContext.getString(isClosed ? R.string.bug_status_closed : R.string.bug_status_opened), DateFormat.getDateInstance().format(date)));
        } catch (ParseException e) {
            e.printStackTrace();
        } finally {
            Trace.endSection();
        }
    }

}

package com.grappbox.grappbox.adapter;

import android.content.Context;
import android.database.Cursor;
import android.database.DatabaseUtils;
import android.database.MatrixCursor;
import android.util.Log;
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
            //"COUNT(" + BugAssignationEntry.TABLE_NAME + "." + BugAssignationEntry._ID + ") AS count_assign"
    };

    public static final int COLUMN_BUG_ID = 0;
    //public static final int COLUMN_COUNT_ASSIGN = 4;
    public static final int COLUMN_TITLE = 1;
    public static final int COLUMN_DELETED_UTC = 2;
    public static final int COLUMN_LAST_EDIT_UTC = 3;
    private static final String LOG_TAG = BugListAdapter.class.getSimpleName();

    private Context mContext;

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
        BugEntryViewHolder vh = (BugEntryViewHolder) view.getTag();
        boolean isClosed = !cursor.isNull(COLUMN_DELETED_UTC);

        vh.mTitle.setText(cursor.getString(COLUMN_TITLE));
        Date date = null;
        try {
            date = Utils.Date.convertUTCToPhone(cursor.getString(isClosed ? COLUMN_DELETED_UTC : COLUMN_LAST_EDIT_UTC));
        } catch (ParseException e) {
            e.printStackTrace();
        }
        vh.mDateStatus.setText(mContext.getString(R.string.bug_status_date, mContext.getString(isClosed ? R.string.bug_status_closed : R.string.bug_status_opened), DateFormat.getDateInstance().format(date)));
        //vh.mNbAssignee.setText(cursor.getString(COLUMN_COUNT_ASSIGN));
    }

    @Override
    public Cursor swapCursor(Cursor newCursor) {

        if (newCursor == null)
            return super.swapCursor(null);
        MatrixCursor cursor = new MatrixCursor(newCursor.getColumnNames());
        if (!newCursor.moveToFirst())
            return super.swapCursor(null);
        do{
            if (newCursor.isNull(COLUMN_TITLE))
                continue;
            MatrixCursor.RowBuilder builder = cursor.newRow();
            for (String column : cursor.getColumnNames()){
                int i = cursor.getColumnIndex(column);
                int type = newCursor.getType(i);
                switch (type){
                    case Cursor.FIELD_TYPE_NULL:
                        builder.add(column, null);
                        break;
                    case Cursor.FIELD_TYPE_INTEGER:
                        builder.add(column, newCursor.getInt(i));
                        break;
                    case Cursor.FIELD_TYPE_FLOAT:
                        builder.add(column, newCursor.getFloat(i));
                        break;
                    case Cursor.FIELD_TYPE_STRING:
                        builder.add(column, newCursor.getString(i));
                        break;
                    default:
                        throw new IllegalArgumentException("Not normally in columns, check database");
                }
            }
        }while (newCursor.moveToNext());

        return super.swapCursor(cursor);
    }
}

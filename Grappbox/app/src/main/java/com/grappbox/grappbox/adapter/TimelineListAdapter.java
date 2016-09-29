package com.grappbox.grappbox.adapter;

import android.content.Context;
import android.database.Cursor;
import android.support.v4.widget.CursorAdapter;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageButton;
import android.widget.ImageView;
import android.widget.TextView;

import com.grappbox.grappbox.R;
import com.grappbox.grappbox.data.GrappboxContract.TimelineMessageEntry;
import com.grappbox.grappbox.data.GrappboxContract.TimelineEntry;

/**
 * Created by tan_f on 28/09/2016.
 */

public class TimelineListAdapter extends CursorAdapter {

    private static final String LOG_TAG = TimelineListAdapter.class.getSimpleName();

    public static final String[] projection = new String[]{
            TimelineMessageEntry.TABLE_NAME + "." + TimelineMessageEntry._ID,
            TimelineMessageEntry.TABLE_NAME + "." + TimelineMessageEntry.COLUMN_TITLE,
            TimelineMessageEntry.TABLE_NAME + "." + TimelineMessageEntry.COLUMN_MESSAGE,
            TimelineMessageEntry.TABLE_NAME + "." + TimelineMessageEntry.COLUMN_DATE_LAST_EDITED_AT_UTC
    };

    public class TimelineEntryViewHolder{
        ImageView   mAvatar;
        TextView    mTitle;
        TextView    mMessage;
        TextView    mLastUpdate;
        ImageButton mAnswer;
        ImageButton mEdit;
        ImageButton mDelete;

        public TimelineEntryViewHolder(View v){
            mAvatar = (ImageView) v.findViewById(R.id.avatar);
            mTitle = (TextView) v.findViewById(R.id.title);
            mMessage = (TextView) v.findViewById(R.id.messagecontent);
            mLastUpdate = (TextView) v.findViewById(R.id.messagelastupdate);
            mAnswer = (ImageButton) v.findViewById(R.id.answer);
            mEdit = (ImageButton) v.findViewById(R.id.edit);
            mDelete = (ImageButton) v.findViewById(R.id.delete);

        }
    }

    private Context mContext;

    public TimelineListAdapter(Context context, Cursor c, int flags)
    {
        super(context, c, flags);
        mContext = context;
    }

    @Override
    public View newView(Context context, Cursor cursor, ViewGroup viewGroup) {

        View v = LayoutInflater.from(context).inflate(R.layout.list_item_timeline_list, viewGroup, false);

        return v;
    }

    @Override
    public void bindView(View view, Context context, Cursor cursor) {

    }
}

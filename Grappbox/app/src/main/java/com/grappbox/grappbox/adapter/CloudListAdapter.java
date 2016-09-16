package com.grappbox.grappbox.adapter;

import android.content.ContentValues;
import android.content.Context;
import android.database.Cursor;
import android.database.DatabaseUtils;
import android.database.MatrixCursor;
import android.database.MergeCursor;
import android.graphics.drawable.Drawable;
import android.support.v4.content.res.ResourcesCompat;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.CursorAdapter;
import android.widget.ImageButton;
import android.widget.ImageView;
import android.widget.TextView;

import com.grappbox.grappbox.R;
import com.grappbox.grappbox.Utils;
import com.grappbox.grappbox.data.GrappboxContract.CloudEntry;

import java.text.DateFormat;
import java.text.ParseException;
import java.util.ArrayList;
import java.util.Date;

/**
 * Created by marcw on 12/09/2016.
 */
public class CloudListAdapter extends CursorAdapter {
    private static final String LOG_TAG = CloudListAdapter.class.getSimpleName();
    public static final int ITEM_SUBHEADER = 0;
    public static final int ITEM_ENTRY = 1;

    public static final String[] cloudProjection = new String[]{
        CloudEntry.TABLE_NAME + "." + CloudEntry._ID,
        CloudEntry.COLUMN_TYPE,
        CloudEntry.COLUMN_FILENAME,
        CloudEntry.COLUMN_PATH,
        CloudEntry.COLUMN_DATE_LAST_EDITED_UTC,
        CloudEntry.COLUMN_SIZE,
        CloudEntry.COLUMN_MIMETYPE
    };
    public static final int COLUMN_ID = 0;
    public static final int COLUMN_TYPE = 1;
    public static final int COLUMN_FILENAME = 2;
    public static final int COLUMN_PATH = 3;
    public static final int COLUMN_LAST_EDITED_UTC = 4;
    public static final int COLUMN_SIZE = 5;
    public static final int COLUMN_MIMETYPE = 6;

    public interface CloudAdapterListener{
        void onMoreClicked(int position);
    }

    public static class SubHeaderViewHolder{
        public TextView subheader;

        public SubHeaderViewHolder(View view){
            subheader = (TextView) view.findViewById(R.id.subheader);
        }
    }

    public static class CloudEntryViewHolder{
        public ImageView mIcon;
        public TextView mFilename;
        public TextView mLastEdited;
        public ImageButton mMore;

        public CloudEntryViewHolder(View view){
            mIcon = (ImageView) view.findViewById(R.id.ic_type);
            mFilename = (TextView) view.findViewById(R.id.filename);
            mLastEdited = (TextView) view.findViewById(R.id.last_modified);
            mMore = (ImageButton) view.findViewById(R.id.more);
        }
    }

    private CloudAdapterListener mListener = null;

    public CloudListAdapter(Context context, Cursor c, int flags) {
        super(context, c, flags);
    }

    public void setListener(CloudAdapterListener listener){ mListener = listener; }

    @Override
    public int getItemViewType(int position) {
        Cursor cursor = (Cursor) this.getItem(position);

        return cursor.getColumnCount() == 2 ? ITEM_SUBHEADER : ITEM_ENTRY;
    }

    @Override
    public int getViewTypeCount() {
        return 2;
    }

    @Override
    public Cursor swapCursor(Cursor newCursor) {
        if (newCursor == null || !newCursor.moveToFirst())
            return super.swapCursor(newCursor);

        MatrixCursor subSafe = new MatrixCursor(new String[]{"_id", "subheader"});
        subSafe.addRow(new Object[]{0, "Secured directory"});
        MatrixCursor subDir = new MatrixCursor(new String[]{"_id", "subheader"});
        subDir.addRow(new Object[]{0, "Directories"});
        MatrixCursor subFile = new MatrixCursor(new String[]{"_id", "subheader"});
        subFile.addRow(new Object[]{0, "Files"});

        MatrixCursor safe = new MatrixCursor(cloudProjection);
        MatrixCursor dirs = new MatrixCursor(cloudProjection);
        MatrixCursor files = new MatrixCursor(cloudProjection);
        do {
            int type = newCursor.getInt(newCursor.getColumnIndex(CloudEntry.COLUMN_TYPE));
            int i = 0;
            ContentValues rowValue = new ContentValues();
            DatabaseUtils.cursorRowToContentValues(newCursor, rowValue);
            MatrixCursor.RowBuilder builder;
            switch (type){
                case 2:
                    builder = safe.newRow();
                    break;
                case 1:
                    builder = dirs.newRow();
                    break;
                default:
                    builder = files.newRow();
                    break;
            }
            for (String colName : cloudProjection) {
                switch (newCursor.getType(i)){
                    case Cursor.FIELD_TYPE_NULL:
                        builder.add(colName, null);
                        break;
                    case Cursor.FIELD_TYPE_INTEGER:
                        builder.add(colName, newCursor.getInt(i));
                        break;
                    case Cursor.FIELD_TYPE_FLOAT:
                        builder.add(colName, newCursor.getFloat(i));
                        break;
                    case Cursor.FIELD_TYPE_STRING:
                        builder.add(colName, newCursor.getString(i));
                        break;
                    default:
                        throw new IllegalArgumentException("Not normally in columns, check database");
                }
                ++i;
            }
        } while (newCursor.moveToNext());

        ArrayList<Cursor> finals = new ArrayList<>();
        if (safe.getCount() > 0){
            finals.add(subSafe);
            finals.add(safe);
        }
        if (dirs.getCount() > 0){
            finals.add(subDir);
            finals.add(dirs);
        }
        if (files.getCount() > 0){
            finals.add(subFile);
            finals.add(files);
        }
        Cursor[] finalArray = finals.toArray(new Cursor[finals.size()]);
        return super.swapCursor(new MergeCursor(finalArray));
    }

    @Override
    public View newView(Context context, Cursor cursor, ViewGroup viewGroup) {
        int layout;
        int type = getItemViewType(cursor.getPosition());

        switch (type){
            case ITEM_ENTRY:
                layout = R.layout.list_item_file_cloudentry;
                break;
            default:
                layout = R.layout.list_item_cloud_subheader;
                break;
        }
        View v = LayoutInflater.from(context).inflate(layout, viewGroup, false);
        v.setTag(type == ITEM_ENTRY ? new CloudEntryViewHolder(v) : new SubHeaderViewHolder(v));
        return v;
    }

    private void setClickListeners(CloudEntryViewHolder viewHolder, final int position){

        viewHolder.mMore.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                if (mListener != null)
                    mListener.onMoreClicked(position);
            }
        });
    }


    @Override
    public void bindView(View view, Context context, final Cursor cursor) {
        Object viewHolder = view.getTag();
        int type = getItemViewType(cursor.getPosition());

        if (viewHolder instanceof SubHeaderViewHolder){
            ((SubHeaderViewHolder) viewHolder).subheader.setText(cursor.getString(1));
        } else if (viewHolder instanceof CloudEntryViewHolder){
            Drawable ic = ResourcesCompat.getDrawable(view.getResources(), cursor.getInt(COLUMN_TYPE) > 0 ? R.drawable.ic_folder : R.drawable.ic_file, context.getTheme());
            ((CloudEntryViewHolder) viewHolder).mIcon.setImageDrawable(ic);
            if (cursor.isNull(COLUMN_LAST_EDITED_UTC)){
                ((CloudEntryViewHolder) viewHolder).mLastEdited.setVisibility(View.GONE);
            } else {
                try {
                    Date phoneLastModified;
                    phoneLastModified = Utils.Date.convertUTCToPhone(new Date(cursor.getLong(COLUMN_LAST_EDITED_UTC)));
                    ((CloudEntryViewHolder) viewHolder).mLastEdited.setText(DateFormat.getDateInstance().format(phoneLastModified));
                } catch (ParseException e) {
                    e.printStackTrace();
                    ((CloudEntryViewHolder) viewHolder).mLastEdited.setText(R.string.error_unknown_last_modified);
                }
            }
            ((CloudEntryViewHolder) viewHolder).mFilename.setText(cursor.getString(COLUMN_FILENAME));
            if (cursor.getInt(COLUMN_TYPE) == 2){
                ((CloudEntryViewHolder) viewHolder).mMore.setVisibility(View.INVISIBLE);
            }
            setClickListeners((CloudEntryViewHolder) viewHolder, cursor.getPosition());
        }
    }
}

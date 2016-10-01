package com.grappbox.grappbox.model;

import android.content.Context;
import android.database.Cursor;
import android.os.Parcel;
import android.os.Parcelable;
import android.support.v4.util.Pair;
import android.util.Log;

import com.grappbox.grappbox.R;
import com.grappbox.grappbox.Utils;
import com.grappbox.grappbox.data.GrappboxContract;

import java.text.DateFormat;
import java.text.ParseException;
import java.util.Collection;
import java.util.Locale;

/**
 * Created by marc on 30/09/2016.
 */

public class BugModel implements Parcelable {
    private static final String LOG_TAG = BugModel.class.getSimpleName();
    public int _id;
    public String grappboxId, title, desc;
    public boolean isClosed;

    /*
        The following data are considered as additional data.
        It's recommended to lazy load it.
        Additional data have to be set with the setAdditionalData function.
     */
    public long assigneeCount, commentsCount;
    public Collection<Pair<String, String>> tags;

    public BugModel(Context context, Cursor cursor){
        isClosed = cursor.getString(cursor.getColumnIndex(GrappboxContract.BugEntry.COLUMN_DATE_DELETED_UTC)) != null && !cursor.getString(cursor.getColumnIndex(GrappboxContract.BugEntry.COLUMN_DATE_DELETED_UTC)).isEmpty();
        _id = cursor.getInt(cursor.getColumnIndex(GrappboxContract.BugEntry._ID));
        grappboxId = cursor.getString(cursor.getColumnIndex(GrappboxContract.BugEntry.COLUMN_GRAPPBOX_ID));
        title = cursor.getString(cursor.getColumnIndex(GrappboxContract.BugEntry.COLUMN_TITLE));
        try {
            desc = context.getString(R.string.bug_status_date, context.getString(isClosed ? R.string.bug_status_closed : R.string.bug_status_opened), DateFormat.getDateInstance(DateFormat.SHORT, Locale.getDefault()).format(Utils.Date.convertUTCToPhone(cursor.getString(cursor.getColumnIndex(isClosed ? GrappboxContract.BugEntry.COLUMN_DATE_DELETED_UTC : GrappboxContract.BugEntry.COLUMN_DATE_LAST_EDITED_UTC)))));
        } catch (ParseException e) {
            e.printStackTrace();
            desc = context.getString(R.string.error_unknown_last_modified);
        }
        assigneeCount = 0;
        commentsCount = 0;
    }

    protected BugModel(Parcel in) {
        _id = in.readInt();
        grappboxId = in.readString();
        title = in.readString();
        desc = in.readString();
        assigneeCount = in.readLong();
        commentsCount = in.readLong();
    }

    public void setAdditionalData(long assigneeCount, long commentsCount, Collection<Pair<String, String>> tags){
        this.assigneeCount = assigneeCount;
        this.commentsCount = commentsCount;
        this.tags = tags;
    }


    @Override
    public int describeContents() {
        return 0;
    }

    @Override
    public void writeToParcel(Parcel dest, int flags) {
        dest.writeInt(_id);
        dest.writeString(grappboxId);
        dest.writeString(title);
        dest.writeString(desc);
        dest.writeLong(assigneeCount);
        dest.writeLong(commentsCount);
    }

    public static final Creator<BugModel> CREATOR = new Creator<BugModel>() {
        @Override
        public BugModel createFromParcel(Parcel in) {
            return new BugModel(in);
        }

        @Override
        public BugModel[] newArray(int size) {
            return new BugModel[size];
        }
    };
}

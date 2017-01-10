package com.grappbox.grappbox.model;

import android.database.Cursor;
import android.os.Parcel;
import android.os.Parcelable;

import com.grappbox.grappbox.data.GrappboxContract.NextMeetingEntry;

/**
 * Created by tan_f on 10/01/2017.
 */

public class NextMeetingModel implements Parcelable {

    public int      _id;
    public String   _title;
    public String   _desc;
    public String   _date_begin;
    public String   _date_end;

    public NextMeetingModel(Cursor cursor) {
        super();
        _id = cursor.getInt(cursor.getColumnIndex(NextMeetingEntry._ID));
        _title = cursor.getString(cursor.getColumnIndex(NextMeetingEntry.COLUMN_TITLE));
        _desc = cursor.getString(cursor.getColumnIndex(NextMeetingEntry.COLUMN_DESCRIPTION));
        _date_begin = cursor.getString(cursor.getColumnIndex(NextMeetingEntry.COLUMN_BEGIN_DATE));
        _date_end = cursor.getString(cursor.getColumnIndex(NextMeetingEntry.COLUMN_END_DATE));
    }

    public NextMeetingModel(Parcel source) {
        _id = source.readInt();
        _title = source.readString();
        _desc = source.readString();
        _date_begin = source.readString();
        _date_end = source.readString();
    }

    @Override
    public void writeToParcel(Parcel dest, int flags) {
        dest.writeInt(_id);
        dest.writeString(_title);
        dest.writeString(_desc);
        dest.writeString(_date_begin);
        dest.writeString(_date_end);
    }

    @Override
    public int describeContents() {
        return 0;
    }

    public static final Parcelable.Creator<NextMeetingModel> CREATOR = new Parcelable.Creator<NextMeetingModel>() {

        @Override
        public NextMeetingModel createFromParcel(Parcel source) {
            return new NextMeetingModel(source);
        }

        @Override
        public NextMeetingModel[] newArray(int size) {
            return new NextMeetingModel[size];
        }
    };
}

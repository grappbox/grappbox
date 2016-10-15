package com.grappbox.grappbox.model;

import android.content.Context;
import android.database.Cursor;
import android.os.Parcel;
import android.os.Parcelable;

import com.grappbox.grappbox.R;
import com.grappbox.grappbox.Utils;
import com.grappbox.grappbox.data.GrappboxContract;

import java.text.DateFormat;
import java.text.ParseException;
import java.util.ArrayList;
import java.util.List;
import java.util.Locale;

/**
 * Created by tan_f on 10/10/2016.
 */

public class TimelineModel implements Parcelable {

    private static final String LOG_TAG = TimelineModel.class.getSimpleName();
    public int   _id;
    public String   _title;
    public String   _message;
    public String   _lastUpadte;
    public long      _timelineType;
    public int      _countAnswer;
    public long     _timelineId;
    public List<TimelineMessageCommentModel>    mComments;



    public TimelineModel(Context context, Cursor cursor){
        _id = cursor.getInt(cursor.getColumnIndex(GrappboxContract.TimelineMessageEntry._ID));
        _title = cursor.getString(cursor.getColumnIndex(GrappboxContract.TimelineMessageEntry.COLUMN_TITLE));
        _message = cursor.getString(cursor.getColumnIndex(GrappboxContract.TimelineMessageEntry.COLUMN_MESSAGE));
        try {
            _lastUpadte = DateFormat.getDateInstance(DateFormat.MEDIUM, Locale.getDefault()).format(Utils.Date.convertUTCToPhone(cursor.getString(cursor.getColumnIndex(GrappboxContract.TimelineMessageEntry.COLUMN_DATE_LAST_EDITED_AT_UTC))));
        } catch (ParseException e) {
            e.printStackTrace();
            _lastUpadte = context.getString(R.string.error_unknown_last_modified);
        }
        _timelineType = cursor.getLong(cursor.getColumnIndex(GrappboxContract.TimelineEntry.COLUMN_TYPE_ID));
        _countAnswer = cursor.getInt(cursor.getColumnIndex(GrappboxContract.TimelineMessageEntry.COLUMN_COUNT_ANSWER));
        mComments = new ArrayList<>();
    }

    public TimelineModel(Parcel source){
        _id = source.readInt();
        _title = source.readString();
        _message = source.readString();
        _lastUpadte = source.readString();
        _timelineType = source.readInt();
        _countAnswer = source.readInt();
        mComments = new ArrayList<>();
        Parcelable[] arrayCom = source.readParcelableArray(TimelineMessageCommentModel.class.getClassLoader());
        for (Parcelable com : arrayCom){
            mComments.add((TimelineMessageCommentModel) com);
        }
    }

    public void setMessageCommentsData(List<TimelineMessageCommentModel> commentsData){
        mComments = commentsData;
    }

    public TimelineModel(Cursor data){
        _id = data.getInt(data.getColumnIndex(GrappboxContract.TimelineMessageEntry._ID));
        _title = data.getString(data.getColumnIndex(GrappboxContract.TimelineMessageEntry.COLUMN_TITLE));
        _message = data.getString(data.getColumnIndex(GrappboxContract.TimelineMessageEntry.COLUMN_MESSAGE));
        _lastUpadte = data.getString(data.getColumnIndex(GrappboxContract.TimelineMessageEntry.COLUMN_DATE_LAST_EDITED_AT_UTC));
        _timelineType = data.getLong(data.getColumnIndex(GrappboxContract.TimelineEntry.COLUMN_TYPE_ID));
        _countAnswer = data.getInt(data.getColumnIndex(GrappboxContract.TimelineMessageEntry.COLUMN_COUNT_ANSWER));
    }

    @Override
    public int describeContents() {
        return 0;
    }

    @Override
    public void writeToParcel(Parcel dest, int flags) {
        dest.writeInt(_id);
        dest.writeString(_title);
        dest.writeString(_message);
        dest.writeString(_lastUpadte);
        dest.writeLong(_timelineType);
        dest.writeParcelableArray(mComments.toArray(new TimelineMessageCommentModel[mComments.size()]), 0);
        dest.writeInt(_countAnswer);
    }

    public static final Creator<TimelineModel> CREATOR = new Creator<TimelineModel>() {
        @Override
        public TimelineModel createFromParcel(Parcel source) {
            return new TimelineModel(source);
        }

        @Override
        public TimelineModel[] newArray(int size) {
            return new TimelineModel[size];
        }
    };
}

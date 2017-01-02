package com.grappbox.grappbox.model;

import android.content.Context;
import android.database.Cursor;
import android.os.Parcel;
import android.os.Parcelable;

import com.grappbox.grappbox.data.GrappboxContract;

import java.util.ArrayList;
import java.util.Date;

/**
 * Created by Arka on 01/01/2017.
 */

public class StatAdvancementModel implements Parcelable {

    private static final String LOG_TAG = StatAdvancementModel.class.getSimpleName();
    public int     _id;
    public Long    _percentage;
    public Long    _progress;
    public Long    _totalTask;
    public Long    _finishedTask;
    public String  _date;

    public StatAdvancementModel(Context context, Cursor cursor){
        _id = cursor.getInt(cursor.getColumnIndex(GrappboxContract.AdvancementEntry._ID));
        _percentage = cursor.getLong(cursor.getColumnIndex(GrappboxContract.AdvancementEntry.COLUMN_PERCENTAGE));
        _progress = cursor.getLong(cursor.getColumnIndex(GrappboxContract.AdvancementEntry.COLUMN_PROGRESS));
        _totalTask = cursor.getLong(cursor.getColumnIndex(GrappboxContract.AdvancementEntry.COLUMN_TOTAL_TASK));
        _finishedTask = cursor.getLong(cursor.getColumnIndex(GrappboxContract.AdvancementEntry.COLUMN_FINISHED_TASk));
        _date = cursor.getString(cursor.getColumnIndex(GrappboxContract.AdvancementEntry.COLUMN_ADVANCEMENT_DATE));
    }

    protected StatAdvancementModel(Parcel source) {
        _id = source.readInt();
        _percentage = source.readLong();
        _progress = source.readLong();
        _totalTask = source.readLong();
        _finishedTask = source.readLong();
        _date = source.readString();
    }

    @Override
    public void writeToParcel(Parcel dest, int flags) {
        dest.writeInt(_id);
        dest.writeLong(_percentage);
        dest.writeLong(_progress);
        dest.writeLong(_totalTask);
        dest.writeLong(_finishedTask);
        dest.writeString(_date);
    }

    @Override
    public int describeContents() {
        return 0;
    }

    public static final Creator<StatAdvancementModel> CREATOR = new Creator<StatAdvancementModel>() {
        @Override
        public StatAdvancementModel createFromParcel(Parcel source) {
            return new StatAdvancementModel(source);
        }

        @Override
        public StatAdvancementModel[] newArray(int size) {
            return new StatAdvancementModel[size];
        }
    };
}

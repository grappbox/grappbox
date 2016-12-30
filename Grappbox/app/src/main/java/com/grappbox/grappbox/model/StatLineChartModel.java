package com.grappbox.grappbox.model;

import android.content.Context;
import android.database.Cursor;
import android.os.Parcel;
import android.os.Parcelable;

import com.github.mikephil.charting.data.Entry;

import java.util.ArrayList;

/**
 * Created by tan_f on 30/12/2016.
 */

public class StatLineChartModel implements Parcelable {

    private static final String LOG_TAG = StatLineChartModel.class.getSimpleName();

    ArrayList<Integer> entryArrayList;

    public StatLineChartModel(Context context, Cursor cursor){

    }

    protected StatLineChartModel(Parcel source){

    }

    @Override
    public void writeToParcel(Parcel dest, int flags) {

    }

    @Override
    public int describeContents() {
        return 0;
    }

    public static final  Creator<StatLineChartModel> CREATOR = new Creator<StatLineChartModel>() {
        @Override
        public StatLineChartModel createFromParcel(Parcel source) {
            return new StatLineChartModel(source);
        }

        @Override
        public StatLineChartModel[] newArray(int size) {
            return new StatLineChartModel[size];
        }
    };
}

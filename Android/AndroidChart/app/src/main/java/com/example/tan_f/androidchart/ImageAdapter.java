package com.example.tan_f.androidchart;

import android.content.Context;
import android.view.View;
import android.view.ViewGroup;
import android.widget.BaseAdapter;
import android.widget.GridView;
import android.widget.ImageView;

/**
 * Created by tan_f on 31/01/2016.
 */
public class ImageAdapter extends BaseAdapter {
    private Context mContext;

    public ImageAdapter(Context c) {
        mContext = c;
    }

    public int getCount() {
        return 100;
//        return mThumbIds.length;
    }

    public Object getItem(int position) {
        return null;
    }

    public long getItemId(int position) {
        return 0;
    }

    // create a new ImageView for each item referenced by the Adapter
    public View getView(int position, View convertView, ViewGroup parent) {
        ImageView imageView;
        if (convertView == null) {
            // if it's not recycled, initialize some attributes
            imageView = new ImageView(mContext);
            imageView.setLayoutParams(new GridView.LayoutParams(85, 85));
            imageView.setScaleType(ImageView.ScaleType.CENTER_CROP);
            switch (position % 3){
                case 0:
                    imageView.setBackgroundColor(mContext.getResources().getColor(R.color.colorAccent));
                    break;

                case 1:
                    imageView.setBackgroundColor(mContext.getResources().getColor(R.color.colorPrimary));
                    break;

                default:
                    imageView.setBackgroundColor(mContext.getResources().getColor(R.color.caseGrid));
                    break;
            }

            imageView.setPadding(8, 8, 8, 8);
        } else {
            imageView = (ImageView) convertView;
        }

//        imageView.setImageResource(mThumbIds[position]);
        return imageView;
    }

    // references to our images
/*    private Integer[] mThumbIds = {
            mContext.getResources().getColor(R.color.caseGrid), mContext.getResources().getColor(R.color.caseGrid),
            mContext.getResources().getColor(R.color.caseGrid), mContext.getResources().getColor(R.color.caseGrid),
            mContext.getResources().getColor(R.color.caseGrid), mContext.getResources().getColor(R.color.caseGrid),
            mContext.getResources().getColor(R.color.caseGrid), mContext.getResources().getColor(R.color.caseGrid),
            mContext.getResources().getColor(R.color.caseGrid), mContext.getResources().getColor(R.color.caseGrid),
            mContext.getResources().getColor(R.color.caseGrid), mContext.getResources().getColor(R.color.caseGrid),
            mContext.getResources().getColor(R.color.caseGrid), mContext.getResources().getColor(R.color.caseGrid),
            mContext.getResources().getColor(R.color.caseGrid), mContext.getResources().getColor(R.color.caseGrid),
            mContext.getResources().getColor(R.color.caseGrid), mContext.getResources().getColor(R.color.caseGrid),
            mContext.getResources().getColor(R.color.caseGrid), mContext.getResources().getColor(R.color.caseGrid),
            mContext.getResources().getColor(R.color.caseGrid), mContext.getResources().getColor(R.color.caseGrid)
    };*/
}

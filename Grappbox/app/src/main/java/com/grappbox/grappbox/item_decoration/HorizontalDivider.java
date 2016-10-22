package com.grappbox.grappbox.item_decoration;

import android.graphics.Canvas;
import android.graphics.Paint;
import android.support.v7.widget.RecyclerView;
import android.view.View;

/**
 * Created by marcw on 02/10/2016.
 */

public class HorizontalDivider extends RecyclerView.ItemDecoration {

    private Paint mPainter;

    public HorizontalDivider(int color) {
        mPainter = new Paint();
        mPainter.setColor(color);
        mPainter.setStyle(Paint.Style.FILL);
    }

    @Override
    public void onDrawOver(Canvas c, RecyclerView parent, RecyclerView.State state) {
        super.onDrawOver(c, parent, state);
        if (state.isPreLayout() || state.isMeasuring())
            return;
        int left = parent.getPaddingLeft();
        int right = parent.getWidth() - parent.getPaddingRight();
        int childCount = parent.getChildCount();
        for (int i = 0; i < childCount; ++i){
            View item = parent.getChildAt(i);
            RecyclerView.LayoutParams params = (RecyclerView.LayoutParams) item.getLayoutParams();

            int top = item.getBottom() + params.bottomMargin;
            int bottom = top + 1;
            c.drawLine(left, top, right, bottom, mPainter);
        }

    }
}

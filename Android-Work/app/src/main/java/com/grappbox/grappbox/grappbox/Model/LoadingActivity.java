package com.grappbox.grappbox.grappbox.Model;

import android.graphics.drawable.Drawable;
import android.support.v4.content.ContextCompat;
import android.support.v4.graphics.drawable.DrawableCompat;
import android.support.v7.app.AppCompatActivity;
import android.view.View;
import android.widget.ProgressBar;

import com.grappbox.grappbox.grappbox.R;

import java.util.ArrayList;

/**
 * Created by tan_f on 14/07/2016.
 */
public class LoadingActivity extends AppCompatActivity {

    private View[] _hiddenInLoading;
    private ProgressBar _loader;

    public void startLoading(int progressID,  View... hide)
    {
        _hiddenInLoading = hide;
        for (View v : _hiddenInLoading)
            v.setVisibility(View.GONE);
        _loader = (ProgressBar)findViewById(progressID);
        _loader.setVisibility(View.VISIBLE);
        Drawable ld = _loader.getIndeterminateDrawable();
        DrawableCompat.setTint(ld, ContextCompat.getColor(this, R.color.colorGrappboxRed));
        _loader.setIndeterminateDrawable(ld);
    }

    public void startLoading(int progressID, int... hideIDs)
    {
        ArrayList<View> toHide = new ArrayList<>();

        for (int id : hideIDs)
            toHide.add(findViewById(id));
        Object[] toConvert = toHide.toArray();
        _hiddenInLoading = new View[toHide.size()];
        int i = 0;
        for (Object obj : toConvert)
            _hiddenInLoading[i++] = (View) obj;
        for (View v : _hiddenInLoading)
            v.setVisibility(View.GONE);
        _loader = (ProgressBar)findViewById(progressID);
        _loader.setVisibility(View.VISIBLE);
        Drawable ld = _loader.getIndeterminateDrawable();
        DrawableCompat.setTint(ld, ContextCompat.getColor(this, R.color.colorGrappboxRed));
        _loader.setIndeterminateDrawable(ld);
    }

    public void endLoading()
    {
        _loader.setVisibility(View.GONE);
        for (View v : _hiddenInLoading)
            v.setVisibility(View.VISIBLE);
    }

}


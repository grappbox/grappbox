package com.grappbox.grappbox.grappbox.Whiteboard;

import android.graphics.Matrix;
import android.graphics.Paint;
import android.graphics.Path;
import android.graphics.Rect;

/**
 * Created by tan_f on 28/12/2015.
 */
public class DrawingText {

    private Matrix _matrix = new Matrix();
    String _Text;
    Path _PathText;
    Paint _PaintText;
    Rect _SizeText;

    DrawingText(String text, Path pathText, Paint paintText, Rect sizeText)
    {
        _Text = text;
        _PathText = pathText;
        _PaintText = paintText;
        _SizeText = sizeText;
    }

    public void scalePath(float scale)
    {
        _matrix.setScale(scale, scale, 0, 0);
        _PathText.transform(_matrix);
    }

    public void TranslatePath(float x, float y)
    {
        _matrix.setTranslate(x, y);
        _PathText.transform(_matrix);
    }

    public String getText()
    {
        return _Text;
    }

    public Path getPathText()
    {
        return _PathText;
    }

    public Paint getPaintText()
    {
        return _PaintText;
    }

    public Rect getSizeText()
    {
        return _SizeText;
    }
}

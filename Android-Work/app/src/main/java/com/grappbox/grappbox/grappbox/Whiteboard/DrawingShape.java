package com.grappbox.grappbox.grappbox.Whiteboard;

import android.graphics.Matrix;
import android.graphics.Paint;
import android.graphics.Path;
import android.graphics.Rect;
import android.graphics.RectF;

/**
 * Created by tan_f on 28/12/2015.
 */
public class DrawingShape {

    public enum typeShape {
        SHAPE,
        TEXT
    }

    private typeShape _typeShape;
    private Matrix _matrix = new Matrix();
    private String _text = null;
    private float _sizeText = 14;
    private boolean _italicText = false;
    private boolean _boldText = false;
    private Path _path;
    private Paint _InPaint;
    private Paint _OutPaint;
    private int _id = 0;
    private float _ray = -1;
    private RectF _rect = null;

    DrawingShape(typeShape shape, Path path, RectF rect)
    {
        _typeShape = shape;
        _path = path;
        _path.transform(_matrix);
        _rect = rect;
    }

    DrawingShape(typeShape shape, Path path, RectF rect, Paint paint, Paint outPaint, float scale)
    {
        _typeShape = shape;
        _path = path;
        _matrix.setScale(scale, scale);
        _rect = rect;
        _path.transform(_matrix);
        _InPaint = paint;
        _OutPaint = outPaint;
    }

    public void scalePath(float scale)
    {
        _matrix.setScale(scale, scale, 0, 0);
        _path.transform(_matrix);
    }

    public void TranslatePath(float x, float y) {
        _matrix.setTranslate(x, y);
        _path.transform(_matrix);
        if (_typeShape == typeShape.TEXT)
            _rect.inset(x, y);
    }

    public void setText(String text, boolean italic, boolean bold, float sizeText)
    {
        _text = text;
        _italicText = italic;
        _boldText = bold;
        _sizeText = sizeText;
    }

    public void setRay(float ray) {
        _ray =  ray;
    }

    public void setPaint(Paint inPaint, Paint outPaint)
    {
        _InPaint = inPaint;
        _OutPaint = outPaint;
    }

    public void setId(int newID)
    {
        _id = newID;
    }

    public String getText() { return _text; }

    public Path getPath() { return _path; }

    public Paint getInPaint()
    {
        return _InPaint;
    }

    public Paint getOutPaint()
    {
        return _OutPaint;
    }

    public int getId() { return _id; }

    public float getRay() { return _ray; }

    public RectF getRectPosition() { return _rect; }

    public typeShape getTypeShape() { return _typeShape; }

    public boolean isItalicText() { return _italicText; }

    public boolean isBoldText() { return _boldText; }

    public float getSizeText() { return _sizeText; }
}

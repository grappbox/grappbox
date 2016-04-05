package com.grappbox.grappbox.grappbox.Whiteboard;

import android.graphics.Matrix;
import android.graphics.Paint;
import android.graphics.Path;

/**
 * Created by tan_f on 28/12/2015.
 */
public class DrawingShape {

    private Matrix _matrix = new Matrix();
    private Path _Path;
    private Paint _InPaint;
    private Paint _OutPaint;

    DrawingShape(Path path, Paint paint, Paint outPaint, float scale)
    {
        _Path = path;
        _matrix.setScale(scale, scale);
        _Path.transform(_matrix);
        _InPaint = paint;
        _OutPaint = outPaint;
    }

    public void scalePath(float scale)
    {
        _matrix.setScale(scale, scale, 0, 0);
        _Path.transform(_matrix);
    }

    public void TranslatePath(float x, float y)
    {
        _matrix.setTranslate(x, y);
        _Path.transform(_matrix);
    }

    public Path getPath()
    {
        return _Path;
    }

    public Paint getInPaint()
    {
        return _InPaint;
    }

    public Paint getOutPaint()
    {
        return _OutPaint;
    }

}

package com.grappbox.grappbox.grappbox;

import android.graphics.Paint;
import android.graphics.Path;

/**
 * Created by tan_f on 28/12/2015.
 */
public class DrawingShape {

    private Path _Path;
    private Paint _InPaint;
    private Paint _OutPaint;

    DrawingShape(Path path, Paint paint, Paint outPaint)
    {
        _Path = path;
        _InPaint = paint;
        _OutPaint = outPaint;
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

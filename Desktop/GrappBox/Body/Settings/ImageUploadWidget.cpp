#include "Body/Settings/ImageUploadWidget.h"

ImageUploadWidget::ImageUploadWidget(QWidget *parent) : QWidget(parent)
{
    _mainLayout = new QGridLayout(this);
    _imagePresented = new QGraphicsView();
    _scene = new QGraphicsScene(0,0, 128, 128);
    _filenameSelected = new QLabel(tr("Select a file..."));
    _selectFileBtn = new QPushButton(tr("Select image"));
    _filename = QString("");
    _currentPixmap = NULL;
    _fitImg = new QPushButton(tr("Fit"));

    _imagePresented->setMaximumSize(IMG_PREVIEW_SIZE);
    _imagePresented->setMinimumSize(IMG_PREVIEW_SIZE);
    _imagePresented->setScene(_scene);
    _imagePresented->setHorizontalScrollBarPolicy(Qt::ScrollBarAlwaysOff);
    _imagePresented->setVerticalScrollBarPolicy(Qt::ScrollBarAlwaysOff);

    QObject::connect(_selectFileBtn, SIGNAL(released()), this, SLOT(OnImageSelectTriggered()));
    QObject::connect(_fitImg, SIGNAL(released()), this, SLOT(OnFitTriggered()));
    QObject::connect(_scene, SIGNAL(changed(QList<QRectF>)), this, SLOT(SceneChanged(QList<QRectF>)));

    _mainLayout->addWidget(_imagePresented, 0, 0, 3, 3);
    _mainLayout->addWidget(_fitImg, 3, 0, 3, 1);
    _mainLayout->addWidget(_selectFileBtn, 1, 3, 1, 2);
    _mainLayout->addWidget(_filenameSelected, 1, 7, 1, 3);
}

ImageUploadWidget::~ImageUploadWidget()
{

}

void ImageUploadWidget::setImage(const QPixmap &pixmap)
{
    QPixmap img(pixmap);

    _imagePresented->scene()->clear();
    _currentPixmap = _scene->addPixmap(img);
    _currentPixmap->setFlag(QGraphicsItem::ItemIsSelectable);
    _currentPixmap->setFlag(QGraphicsItem::ItemIsMovable);
    _currentPixmap->setFlag(QGraphicsItem::ItemSendsScenePositionChanges);
    _imagePresented->update();
}

const QPixmap   &ImageUploadWidget::getImage()
{
    _lastTakenImage = QPixmap::grabWidget(_imagePresented);
    return _lastTakenImage;
}

bool            ImageUploadWidget::isImageFromComputer()
{
    return (_filename != "");
}

void ImageUploadWidget::OnImageSelectTriggered()
{
    _filename =  QFileDialog::getOpenFileName(this, tr("Open Image"), QStandardPaths::displayName(QStandardPaths::PicturesLocation), tr("Image Files (*.png *.jpg *.bmp)"));
    _filenameSelected->setText(_filename);

    if (_filename != NULL && _filename != "")
    {
        QPixmap img(_filename);

        _imagePresented->scene()->clear();
        _currentPixmap = _scene->addPixmap(img);
        _currentPixmap->setFlag(QGraphicsItem::ItemIsSelectable);
        _currentPixmap->setFlag(QGraphicsItem::ItemIsMovable);
        _currentPixmap->setFlag(QGraphicsItem::ItemSendsScenePositionChanges);
        _imagePresented->update();
        emit OnImageSelected();
    }
}

void ImageUploadWidget::OnFitTriggered()
{
    QPixmap pix;
    if (!_currentPixmap)
        return;

    pix = _currentPixmap->pixmap().copy().scaled(IMG_PREVIEW_SIZE);
    _scene->clear();
    _currentPixmap = _scene->addPixmap(pix);
    _currentPixmap->setFlag(QGraphicsItem::ItemIsSelectable);
    _currentPixmap->setFlag(QGraphicsItem::ItemIsMovable);
    _currentPixmap->setFlag(QGraphicsItem::ItemSendsScenePositionChanges);
    _imagePresented->update();
}

void ImageUploadWidget::SceneChanged(QList<QRectF>)
{
    QPointF pixPos;
    QPointF newPos;
    if (_currentPixmap == NULL || !_currentPixmap->isActive())
        return;

    pixPos = _currentPixmap->pos();
    newPos = pixPos;
    if (pixPos.x() < -(_currentPixmap->pixmap().size().width() - IMG_PREVIEW_WIDTH))
        newPos.setX(-(_currentPixmap->pixmap().size().width() - IMG_PREVIEW_WIDTH));
    else if (pixPos.x() > 0)
        newPos.setX(0);
    if (pixPos.y() < -(_currentPixmap->pixmap().size().height() - IMG_PREVIEW_HEIGHT))
        newPos.setY(-(_currentPixmap->pixmap().size().height() - IMG_PREVIEW_HEIGHT));
    else if (pixPos.y() > 0)
        newPos.setY(0);
    _currentPixmap->setPos(newPos);
}

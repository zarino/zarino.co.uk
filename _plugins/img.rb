def construct_img_tag(alt, path)
    # Look for a high-res version of the image, eg:
    # /media/image.png and /media/image-1400.png
    high_res_path = path.gsub(/([.]\w+)$/, '-1400\1')
    high_res_image_exists = File.exist?(high_res_path.gsub(/^\//, ''))

    # Include all the srcset magic if there is a
    # high-res version of the image to show.
    if high_res_image_exists
        return '<noscript class="loading-lazy"><img src="%s" alt="%s" srcset="%s 700w, %s 1400w" sizes="(min-width: 768px) 700px, (min-width: 480px) 420px, 100vw" loading="lazy"></noscript>' % [path, alt, path, high_res_path]
    else
        return '<noscript class="loading-lazy"><img src="%s" alt="%s" loading="lazy"></noscript>' % [path, alt]
    end
end

module Jekyll
    class ImgTag < Liquid::Tag
        def initialize(tag_name, markup, tokens)
            super
            # Expects two strings or quoted phrases, eg:
            # {% img "Alt text here" /media/image.png %}
            @args = CSV::parse_line(markup.strip, col_sep: ' ')
        end

        def render(context)
            if @args && @args.size
                return construct_img_tag(@args[0], @args[1])
            end
        end
    end

    class BlockImgTag < Liquid::Block
        def render(context)
            # Arguments are provided in the block body, eg:
            # {% blockimg %}"Alt text here" /media/image.png{% endblockimg %}
            body_on_one_line = super.gsub(/\s+/, ' ').strip
            @args = CSV::parse_line(body_on_one_line, col_sep: ' ')

            if @args && @args.size
                return construct_img_tag(@args[0], @args[1])
            end
        end
    end
end

Liquid::Template.register_tag('img', Jekyll::ImgTag)
Liquid::Template.register_tag('blockimg', Jekyll::BlockImgTag)
